<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ .'/../db/db.inc.php');
require_once(__DIR__ .'/../db/queries.inc.php');
require_once(__DIR__ .'/../utils/pesapal_auth.php');
require_once(__DIR__ .'/../utils/pesapal_status.php');
require_once(__DIR__ .'/../utils/respond.php');
require_once(__DIR__ .'/../utils/logger.php');
require_once(__DIR__ .'/../utils/ip_localle.php');

$ipnLogger = $apiLogger->withName("IPN");

$ipnLogger->withName("IPN")->info('New process transaction request');

$content = trim(file_get_contents("php://input"));

$decoded = json_decode($content, true);

if(!is_array($decoded)) {
    $ipnLogger->withName("IPN")->error('invalid json payload to IPN');
    respondWith(500, "Invalid json");
}
$_SESSION['referer'] = !empty ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL;
$_SESSION['localle'] = getIpLocalle($logger);

$notificationType = $decoded['orderNotificationType'];
$trackingId = $decoded['OrderTrackingId'];
$merchantRef = $decoded['OrderMerchantReference'];
$paymentType = "order";

try {
    $sql = 
        "SELECT 
            o.id as order_id,
            o.paid_amount,
            o.merchant_ref,
            o.tracking_id,
            c.id as customer_id,
            c.business_name as customer_name,
            c.email as customer_mail,
            p.id as plan_id,
            p.`name` as plan_name,
            p.plan_color
        FROM orders o
        JOIN customers c ON c.id = o.customer
        JOIN plans p ON p.id = o.plan
        WHERE tracking_id = ? AND merchant_ref = ?;";
    $stmt = $pdo_conn->prepare($sql);
    $stmt->execute([
        $trackingId,
        $merchantRef
    ]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$order) {
        // look for merchant/tracking id in payment_requests table instead
        $stmt = $pdo_conn->prepare("SELECT * FROM payment_requests WHERE tracking_id = ? OR merchant_ref = ?;");
        $stmt->execute([$trackingId, $merchantRef]);
        $paymentRequest = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$paymentRequest) {
            throw new Exception("No order found for the given tracking and merchant IDs", 404);
        }
        $paymentType = "customPay";
        if(strtolower($paymentRequest['is_paid']) == 'yes') {
            $ipnLogger->withName("IPN")->info("Order already processed", ["orderTrackingId"=>$trackingId,"orderMerchantRef"=>$merchantRef]);
        } else {
            $res = getTransactionStatus($trackingId, $logger);
            if($res['status_code'] == 1) {
                complete_payment_request($pdo_conn, $email_conn, [
                    "merchantRef" => $merchantRef,
                    "trackingId" => $trackingId
                ], $dbLogger);
            }
            $ipnLogger->withName("IPN")->info("New transaction processed", array_merge($completedOrder, ["discount" => $discount]));
        }
    } else {
        if(!empty($order['paid_amount'])) {
            $ipnLogger->withName("IPN")->info("Order already processed", ["orderTrackingId"=>$trackingId,"orderMerchantRef"=>$merchantRef]);
        } else {
            $res = getTransactionStatus($trackingId, $logger);
            // send email with transaction status
            if($res['status_code'] == 1) {
                $download = register_download($pdo_conn, [
                    "ip" => $_SESSION['localle']['ip'],
                    "country_code" => $_SESSION['localle']['country']['iso_code'],
                    "country_name" => $_SESSION['localle']['country']['name'],
                    "referrer" => $_SESSION['referer'],
                    "customer_id" => $order['customer_id'],
                    "plan_id" => $order['plan_id'],
                    "order_id" => $order["order_id"],
                    "is_paid" => true,
                ], $dbLogger);
                
                $completedOrder = complete_order($pdo_conn, $email_conn, [
                    "amount" => $res['amount'],
                    "merchantRef" => $merchantRef,
                    "trackingId" => $trackingId,
                    "download_id" => $download['id']
                ], $dbLogger);
            }
        }
    }
    $ipnLogger->withName("IPN")->info("New $paymentType transaction processed", ["orderTrackingId"=>$trackingId,"orderMerchantRef"=>$merchantRef]);
    echo json_encode(["orderNotificationType" => $notificationType, "orderTrackingId"=>$trackingId,"orderMerchantReference"=>$merchantRef,"status"=>200]);
} catch (Exception $e) {
    $ipnLogger->withName('IPN')->critical("Failed to process transation", ['message' => $e->getMessage(), "orderTrackingId" => $trackingId, "orderMerchantRef" => $merchantRef]);
    echo json_encode(["orderNotificationType" => $notificationType, "orderTrackingId"=>$trackingId,"orderMerchantReference"=>$merchantRef,"status"=>500]);
}