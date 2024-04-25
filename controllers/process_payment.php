<?php
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../db/queries.inc.php');
require_once(__DIR__ . '/../utils/redirect.php');
require_once(__DIR__ . '/../utils/currency_convert.php');
require_once(__DIR__ . '/../utils/load_env.php');
require_once(__DIR__ . '/../utils/logger.php');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_GET['tracking_id'])) {
    throw new Exception("Order tracking id must be supplied for payment processing!", 400);
}

$trackingId = $_GET['tracking_id'];
$logger->withName('Controller')->info('Process payment request', ['trackingId' => $trackingId]);

$confirmation_code = $_POST['confirmation_code'];
if(!$confirmation_code) {
    $_SESSION['transaction_error'] = "No confirmation code set";
    redirect($_SESSION['callback_redirect']."?".http_build_query($_SESSION['callback_redirect_query']));

}
$paymentType = isset($_GET['type']) ? $_GET['type'] : 'order';
$order = null;

if($paymentType == 'custom') {
        
    $stmt = $pdo_conn->prepare("SELECT * FROM payment_requests WHERE tracking_id = ?;");
    $stmt->execute([
        $trackingId
    ]);
    $payment_request = $stmt->fetch(PDO::FETCH_ASSOC);
    if($payment_request) {
        $order = [
            "invoice_amount" => $payment_request['amount'],
            "created_at" => $payment_request['creation_date']
        ];
    } 
} else {
    $stmt = $pdo_conn->prepare("SELECT * FROM orders WHERE tracking_id = ?");
    $stmt->execute([$trackingId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}

if(!$order) {
    throw new Exception("No order or payment request found for the given parameters", 400);
}
// check if confirmation has been used in an order or payment request
foreach (["orders", "payment_requests"] as $table) {
    $stmt = $pdo_conn->prepare("SELECT * FROM $table WHERE confirmation_code = ?;");
    $stmt->execute([$confirmation_code]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if($record) {
        $_SESSION['transaction_error'] = "That confirmation code has already been received, please await payment verification email.";
        redirect($_SESSION['callback_redirect']."?".http_build_query($_SESSION['callback_redirect_query']));
    }
}


try {
    $retries = 5;
    $success = false;

    do {
        try {
            $completedOrder = null;
            if($paymentType !== 'custom') {
                $download = register_download($pdo_conn, [
                    "ip" => $_SESSION['localle']['ip'],
                    "country_code" => $_SESSION['localle']['country']['iso_code'],
                    "country_name" => $_SESSION['localle']['country']['name'],
                    "referrer" => $_SESSION['referer'],
                    "customer_id" => $order['customer'],
                    "plan_id" => $order['plan'],
                    "order_id" => $order["id"],
                    "is_paid" => true,
                ], $dbLogger);
    
                $completedOrder = complete_order($pdo_conn, [
                    "confirmation_code" => $confirmation_code,
                    "tracking_id" => $trackingId,
                    "download_id" => $download['id']
                ], $dbLogger);
                
                if(!$completedOrder) {
                    throw new Exception("Could not complete order", 500);
                }
                // TODO: ADD payment confirmation email
                $confirmationMail = file_get_contents(__DIR__ . '/../email_templates/order_confirmation.html');
                $parsedMail = str_replace([
                    "{customer_name}",
                    "{confirmation_code}",
                    "{invoice_amount}",
                    "{plan_name}",
                    "plan_color",
                    "{download_id}"
                ], [
                    $completedOrder['customer_name'],
                    $completedOrder['confirmation_code'],
                    format_money($completedOrder['currency'], "en_KE", $completedOrder['invoice_amount']),
                    $completedOrder['plan_name'],
                    $completedOrder['plan_color'],
                    !empty($download['id']) ? $download['id'] : NULL,
                ], $confirmationMail);

                $sentmail = send_mail($email_conn, [
                    "to" => $completedOrder['customer_mail'],
                    "about" => "Order Confirmation",
                    "message" => $parsedMail
                ], $dbLogger);
            } else {
                $completedOrder = complete_payment_request($pdo_conn, [
                    "confirmation_code" => $confirmation_code,
                    "tracking_id" => $trackingId
                ], $dbLogger);
                $confirmationMail = file_get_contents(__DIR__ . '/../email_templates/custom_pay_confirmation.html');
                $parsedMail = str_replace([
                    "{customer_name}",
                    "{confirmation_code}",
                    "{invoice_amount}"
                ], [
                    $completedOrder['customer_name'],
                    $completedOrder['confirmation_code'],
                    format_money("KES", "en_KE", $completedOrder['invoice_amount'])
                ], $confirmationMail);

                $sentmail = send_mail($email_conn, [
                    "to" => $completedOrder['customer_mail'],
                    "about" => "Order Confirmation",
                    "message" => $parsedMail
                ], $dbLogger);
            }
            if($completedOrder) {
                $_SESSION['checkout_response'] = [
                    "status_code" => 1,
                    "payment_status_description" => "COMPLETED",
                    "description" => 
                        $paymentType != 'custom' 
                            ? "Your confirmation has been received, you can dowload managex, but you will have your license once your payment is verified (atmost 1 business day)" 
                            : "Your confirmation has been received, Your payment will reflect in managex as soon as it's verified (atmost 1 business day)",
                    "confirmation_code" => $confirmation_code,
                    "currency" => "KES",
                    "download_id" => $download['id'],
                    "amount" => $order['invoice_amount'],
                    "created_date" => date("Y-m-d H:i:s")
                ];
            }
            $success = true; 

            if($_SESSION['checkout_response']['status_code'] == 1) {
                $_SESSION['step'] = $paymentType == 'custom' ? 2 : 3;
                $_SESSION['callback_redirect_query']['status'] = "success";
            }
            redirect($_SESSION['callback_redirect']."?".http_build_query($_SESSION['callback_redirect_query']));
        } catch (Exception $e) {
            $retries--;
            if(!$retries) {
                throw $e;
            }
        }
    } while ($retries && !$success);

} catch (Exception $e) {
    $apiLogger->withName('Controller')->critical("Failed to process transation", ['trackingId' => $trackingId, 'message' => $e->getMessage()]);
    
    $_SESSION['transaction_error'] = $e->getMessage();
    redirect($_SESSION['callback_redirect']."?".http_build_query($_SESSION['callback_redirect_query']));
}