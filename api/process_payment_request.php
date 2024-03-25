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
$apiLogger->info('Create order from payment request');

try {
    $origin = $_ENV['APP_ENV'] == 'development' ? 'http://localhost:3000/managex' : 'https://kingsoft.biz';
    $body = [
        "id" => getRandomString(40),
        "currency" => $_SESSION['payment_request']['currency'],
        "amount" => $_SESSION['payment_request']['amount'],
        "description" => "Payment for Managex software: ",
        "callback_url" => "$origin/controllers/process_payment.php?type=custom",
        "cancellation_url" => "$origin/controllers/cancel_payment.php",
        "notification_id" => "7a029074-90e0-460e-aa87-dd9b2ae0ffd2",
        "billing_address" => [
            "email_address" => $_SESSION['payment_request']['request_email'] ?? NULL,
            "country_code" => $_SESSION['localle']['country']['iso_code'],
        ],
    ];
    $_SESSION['order_request_body'] = $body;
    $res = submitOrderRequest($body, $logger);

    // proceed to custom pay step 2
    $_SESSION['step'] = 2;
    $_SESSION['redirectUrl'] = $res['redirect_url'];

    // setup url params to be used by payment processing script when redirecting
    $parsedReferrer = getUrlParts($_POST['referrer']);
    $_SESSION['callback_redirect'] = $parsedReferrer['url'];
    if($parsedReferrer['query']) {
        $_SESSION['callback_redirect_query'] = $parsedReferrer['query'];
    }
    if(!empty($_POST['email'])) {
        $_SESSION['callback_redirect_query']['email'] = $_POST['email'];
    }
    if(!empty($_POST['managex_code'])) {
        $_SESSION['callback_redirect_query']['managex_code'] = $_POST['managex_code'];
    }

    // save tracking and merchant ID's in session incase of retry
    $_SESSION['merchant_ref'] = $res['merchant_reference'];
    $_SESSION['tracking_id'] = $res['order_tracking_id'];

    // update original request with tracking and merchant ref info for IPN purposes
    $retries = 3;
    $updatedRequest = null;
    do {
        try {
            
            $sql = "UPDATE payment_requests SET tracking_id = ?, merchant_ref = ? WHERE request_email = ? OR request_mgx_code = ?;";
            $stmt = $pdo_conn->prepare($sql);
            $stmt->execute(([
                $res['order_tracking_id'],
                $res['merchant_reference'],
                !empty($_POST['email']) ? $_POST['email'] : '-',
                !empty($_POST['managex_code']) ? $_POST['managex_code'] : '-'
            ]));
            $sql = 
                "SELECT * FROM payment_requests WHERE request_email = ? OR request_mgx_code = ?;";
            $stmt = $pdo_conn->prepare($sql);
            $stmt->execute([
                !empty($_POST["email"]) ? $_POST["email"] : "-",
                !empty($_POST["managex_code"]) ? $_POST["managex_code"] : "-"
            ]);
            $updatedRequest = $stmt->fetch(PDO::FETCH_ASSOC);
            $success = true;
        } catch (Exception $e) {
            $retries--;
            if(!$retries) {
                $dbLogger->critical("Failed to update payment request", [
                    'customer_mail' => !empty($_POST['email']) ? $_POST['email'] : '-', 
                    'customer_managex_code' => !empty($_POST['email']) ? $_POST['email'] : '-',
                    'message' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    } while ($retries > 0 && !$updatedRequest);
    echo json_encode($res);
} catch (Exception $e) {
    $apiLogger->critical("Failed to create pesapal order request", [
        "message" => $e->getMessage(),
        "customer_mail" => !empty($_POST['email']) ? $_POST['email'] : '-',
        "customer_managex_code" => !empty($_POST['email']) ? $_POST['email'] : '-'
    ]);
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}