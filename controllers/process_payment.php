<?php
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../db/queries.inc.php');
require_once(__DIR__ . '/../utils/pesapal_auth.php');
require_once(__DIR__ .'/../utils/pesapal_status.php');
require_once(__DIR__ . '/../utils/redirect.php');
require_once(__DIR__ . '/../utils/load_env.php');
require_once(__DIR__ . '/../utils/logger.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$trackingId = $_GET['OrderTrackingId'];
$merchantRef = $_GET['OrderMerchantReference'];

$logger->withName('Controller')->info('Process payment request', ['trackingId' => $trackingId, 'merchantRed' => $merchantRef]);
$paymentType = isset($_GET['type']) ? $_GET['type'] : 'order';

try {
    $res = getTransactionStatus($trackingId, $logger);

    if (substr($res['payment_account'], 0, 4) == "4000" && $_ENV['APP_ENV'] === 'development') {
        // Emulate a card failure

        $res = [
            "status_code" => 2,
            "payment_status_description" => "FAILED",
            "description" => "You have insufficient funds",
            "payment_method" => "Visa",
            "payment_account" => "4000XXXXXXXX1018",
            "currency" => "KES",
            "amount" => 1250,
            "created_date" => "2024-02-14 16:54:13"
        ];
    }
    
    $status = $res['status_code'];
    $_SESSION['checkout_response'] = $res;
    if($status === 1) {
        if($paymentType !== 'custom') {
            $download = register_download($pdo_conn, [
                "ip" => $_SESSION['localle']['ip'],
                "country_code" => $_SESSION['localle']['country']['iso_code'],
                "country_name" => $_SESSION['localle']['country']['name'],
                "referrer" => $_SESSION['referer'],
                "customer_id" => $_SESSION['order']['customer'],
                "plan_id" => $_SESSION['order']['plan'],
                "order_id" => $_SESSION['order']["id"],
                "is_paid" => true,
            ], $dbLogger);
            
            $_SESSION['checkout_response'] = array_merge($res, ["download_id" => $download['id']]);
            complete_order($pdo_conn, $email_conn, [
                "amount" => $res['amount'],
                "merchantRef" => $merchantRef,
                "trackingId" => $trackingId,
                "download_id" => $download['id']
            ], $dbLogger);
        } else {
            complete_payment_request($pdo_conn, $email_conn, [
                "merchantRef" => $merchantRef,
                "trackingId" => $trackingId
            ], $dbLogger);
        }
        
        
        $_SESSION['step'] = 3;
        $_SESSION['callback_redirect_query']['status'] = "success";
        redirect($_SESSION['callback_redirect']."?".http_build_query($_SESSION['callback_redirect_query']));
    } else {
        $_SESSION['transaction_error'] = $res['description'];

        redirect($_SESSION['callback_redirect']."?".http_build_query($_SESSION['callback_redirect_query']));
    }
} catch (Exception $e) {
    $apiLogger->withName('Controller')->critical("Failed to process transation", ['trackingId' => $trackingId, 'merchantRed' => $merchantRef, 'message' => $e->getMessage()]);
    
    $_SESSION['transaction_error'] = $e->getMessage();
    redirect($_SESSION['callback_redirect']."?".http_build_query($_SESSION['callback_redirect_query']));
}