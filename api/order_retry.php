<?php
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../utils/logger.php');
require_once(__DIR__ . '/../utils/random.php');
require_once(__DIR__ . '/../utils/pesapal_auth.php');
require_once(__DIR__ . '/../utils/pesapal_order.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$apiLogger->info("Retry order request", ['trackingId' => $_SESSION['tracking_id'], 'merchantRef' => $_SESSION['merchant_ref']]);
$paymentType = isset($_GET['type']) ? $_GET['type'] : 'order';

try {
    $res = submitOrderRequest(array_merge($_SESSION['order_request_body'], ['id' => getRandomString(40)]), $logger);
    // Update existing order with new tracking ID and merchant ref
    $retries = 3;
    $success = false;
    do {
        try {
            // TODO: get tracking id and merchant ref from session order object
            $table = $paymentType == 'order' ? "orders" : "payment_requests";
            $stmt = $pdo_conn->prepare("SELECT * FROM $table WHERE tracking_id = ? AND merchant_ref = ?;");
            $stmt->execute([
                $_SESSION['tracking_id'],
                $_SESSION['merchant_ref']
            ]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            if(!$record) {
                throw new Exception("No record found for the given tracking/merchant ref", 404);
            }
            $sql = 
                "UPDATE $table SET tracking_id = ?, merchant_ref = ? WHERE tracking_id = ? AND merchant_ref = ?;";
            $stmt = $pdo_conn->prepare($sql);
            $stmt->execute([
                $res['order_tracking_id'],
                $res['merchant_reference'],
                $_SESSION['tracking_id'],
                $_SESSION['merchant_ref']
            ]);

            $_SESSION['merchant_ref'] = $res['merchant_reference'];
            $_SESSION['tracking_id'] = $res['order_tracking_id'];
            $success = true;
        } catch (Exception $e) {
            if($e->getCode() == 400) {
                throw $e;
            }
            $retries--;
            if(!$retries) {
                $dbLogger->critical("Failed to update existing order for retry", [
                    'paymentType' => $paymentType, 
                    'trackingId' => $_SESSION['tracking_id'], 
                    'merchantRef' => $_SESSION['merchant_ref'],
                    'message' => $e->getMessage(),  
                ]);
                throw $e;
            }
        }
    } while ($retries > 0 && !$success);

    $_SESSION['redirectUrl'] = $res['redirect_url'];
    $_SESSION['checkout_response'] = NULL;
    $_SESSION['transaction_error'] = NULL;
    echo json_encode($res);
} catch (Exception $e) {
    $apiLogger->critical("Failed to complete retry order request", ["message" => $e->getMessage(), "paymentType" => $paymentType]);
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}