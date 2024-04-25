<?php
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../utils/logger.php');
require_once(__DIR__ . '/../utils/random.php');
require_once(__DIR__ . '/../utils/pesapal_auth.php');
require_once(__DIR__ . '/../utils/pesapal_order.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_GET['tracking_id'])) {
    respondWith(500, "Tracking ID not set");
    
}
$trackingId = $_GET['tracking_id'];
$apiLogger->info("Retry order request", ['trackingId' => $trackingId]);
$paymentType = isset($_GET['type']) ? $_GET['type'] : 'order';

try {

    $table = $paymentType == 'order' ? "orders" : "payment_requests";
    $stmt = $pdo_conn->prepare("SELECT * FROM $table WHERE tracking_id = ?;");
    $stmt->execute([
        $trackingId
    ]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$order) {
        throw new Exception("No order found for the given tracking ref", 404);
    }

    $_SESSION['checkout_response'] = NULL;
    $_SESSION['transaction_error'] = NULL;
    echo json_encode($order);
} catch (Exception $e) {
    $apiLogger->critical("Failed to complete retry order request", ["message" => $e->getMessage(), "paymentType" => $paymentType]);
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}