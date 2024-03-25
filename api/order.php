<?php 

use Monolog\Logger;
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../db/queries.inc.php');
require_once(__DIR__ . '/../utils/respond.php');
require_once(__DIR__ . '/../utils/logger.php');
require_once(__DIR__ . '/../utils/random.php');
require_once(__DIR__ . '/../utils/load_env.php');
require_once(__DIR__ . '/../utils/parse_url.php');
require_once(__DIR__ . '/../utils/pesapal_auth.php');
require_once(__DIR__ . '/../utils/pesapal_order.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$apiLogger->info('Create order request');

try {
    $pdo_conn->beginTransaction();
    $customer = register_customer($pdo_conn, $_POST, $dbLogger);
    $_SESSION['customer'] = $customer;

    $origin = $origin = $_ENV['APP_ENV'] == 'development' ? 'http://localhost:3000/managex' : 'https://kingsoft.biz';;

    $body = [
        "id" => getRandomString(40),
        "currency" => $_SESSION['currency'] == 'KES' ? 'KES' : 'USD',
        "amount" => $_POST['total'],
        "description" => "Payment for Managex software plan: " . $_SESSION['plan']['name'],
        "callback_url" => "$origin/controllers/process_payment.php",
        "cancellation_url" => "$origin/controllers/cancel_payment.php",
        "notification_id" => "7a029074-90e0-460e-aa87-dd9b2ae0ffd2",
        "billing_address" => array_filter([
            "email_address" => $_POST['email'],
            "phone_number" => $_POST['phone'] ?? NULL,
            "line_1" => $_POST['address'] ?? NULL,
            "country_code" => $_SESSION['localle']['country']['iso_code'],
            "city" => $_POST['city'] ?? NULL,
            "state" => $_POST['state'] ?? NULL,
            "postal_code" => $_POST['postal_code'] ?? NULL,
        ], function($value) {
            return !is_null($value);
        })
    ];
    $endDate = NULL;
    if($_SESSION['model'] !== 'ONETIME') {
        $_SESSION['plan']['expiry'] = $_POST['end_date'];
        $startDate = new DateTime($_POST['start_date']);
        $endDate = new DateTime($_POST['end_date']);

        $body = array_merge($body, [
            'account_number' => $customer['id'],
            'subscription_details' => [
                'start_date' => $startDate->format('d-m-Y'),
                'end_date' => $endDate->format('d-m-Y'),
                'frequency' => $_SESSION['model']
            ]
        ]);
    }
    $_SESSION['order_request_body'] = $body;

    $parsedReferrer = getUrlParts($_POST['referrer']);
    $_SESSION['callback_redirect'] = $parsedReferrer['url'];
    if($parsedReferrer['query']) {
        $_SESSION['callback_redirect_query'] = $parsedReferrer['query'];
    }
    
    $res = submitOrderRequest($body, $logger);
    // Create order
    $orderDetail = [
        "plan_id" => $_SESSION['plan']['id'],
        "customer_id" => $customer['id'],
        "currency" => $_SESSION['currency'],
        "invoice_amount" => $_POST['total'],
        "merchant_ref" => $res['merchant_reference'],
        "tracking_id" => $res['order_tracking_id'],
        "discounts" => $_SESSION['discounts']
    ];
    $_SESSION['merchant_ref'] = $res['merchant_reference'];
    $_SESSION['tracking_id'] = $res['order_tracking_id'];

    if($_SESSION['model'] !== 'ONETIME') {
        $orderDetail['expiry'] = $endDate->format('Y-m-d H:i:s');
    }

    $retries = 3;
    $order = null;
    do {
        try {
            $order = create_order($pdo_conn, $orderDetail, $dbLogger);
            $_SESSION['order'] = $order;
        } catch (Exception $e) {
            $dbLogger->critical("Failed to create order", [
                'customer' => $_SESSION['customer']['id'], 
                'plan' => $_SESSION['plan']['id'], 
                'message' => $e->getMessage(), 
                'retries_remaining' => $retries
            ]);
            $retries--;
            if(!$retries) {
                throw $e;
            }
        }
    } while ($retries > 0 && !$order);

    $pdo_conn->commit();

    $_SESSION['step'] = 2;
    $_SESSION['redirectUrl'] = $res['redirect_url'];
    echo json_encode($res);
} catch (Exception $e) {
    $pdo_conn->rollBack();
    $apiLogger->critical("Failed to create order", ["message" => $e->getMessage(), "customer" => $_SESSION['customer']['id'], "plan" => $_SESSION['plan']['id']]);
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
} finally {
    if($pdo_conn->inTransaction()) {
        $pdo_conn->rollBack();
    }
}