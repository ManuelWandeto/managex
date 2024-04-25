<?php 

use Monolog\Logger;
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../db/queries.inc.php');
require_once(__DIR__ . '/../utils/respond.php');
require_once(__DIR__ . '/../utils/logger.php');
require_once(__DIR__ . '/../utils/random.php');
require_once(__DIR__ . '/../utils/load_env.php');
require_once(__DIR__ . '/../utils/parse_url.php');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$apiLogger->info('Create order request');

try {
    $pdo_conn->beginTransaction();
    $customer = register_customer($pdo_conn, $_POST, $dbLogger);
    $_SESSION['customer'] = $customer;


    $parsedReferrer = getUrlParts($_POST['referrer']);
    $_SESSION['callback_redirect'] = $parsedReferrer['url'];
    if($parsedReferrer['query']) {
        $_SESSION['callback_redirect_query'] = $parsedReferrer['query'];
    }
    
    // Create order
    $orderDetail = [
        "plan_id" => $_SESSION['plan']['id'],
        "customer_id" => $customer['id'],
        "currency" => 'KES',
        "invoice_amount" => $_POST['total'],
        "tracking_id" => getRandomString(40),
        "discounts" => $_SESSION['discounts']
    ];

    $retries = 3;
    $order = null;
    do {
        try {
            $order = create_order($pdo_conn, $orderDetail, $dbLogger);
            $_SESSION['order'] = $order;
        } catch (Exception $e) {
            $retries--;
            if(!$retries) {
                $dbLogger->critical("Failed to create order", [
                    'customer' => $_SESSION['customer']['id'], 
                    'plan' => $_SESSION['plan']['id'], 
                    'message' => $e->getMessage(), 
                    'retries_remaining' => $retries
                ]);
                throw $e;
            }
        }
    } while ($retries > 0 && !$order);

    $pdo_conn->commit();

    $_SESSION['step'] = 2;
    echo json_encode($order);
} catch (Exception $e) {
    $pdo_conn->rollBack();
    $apiLogger->critical("Failed to create order", ["message" => $e->getMessage(), "customer" => $_SESSION['customer']['id'], "plan" => $_SESSION['plan']['id']]);
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
} finally {
    if($pdo_conn->inTransaction()) {
        $pdo_conn->rollBack();
    }
}