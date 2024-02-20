<?php 

use Monolog\Logger;
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../db/queries.inc.php');
require_once(__DIR__ . '/../utils/respond.php');
require_once(__DIR__ . '/../utils/logger.php');
require_once(__DIR__ . '/../utils/pesapal_auth.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$apiLogger->info('Create order request');

$curl = curl_init();
try {
    $pdo_conn->beginTransaction();
    $customer = register_customer($pdo_conn, $_POST, $dbLogger);
    $_SESSION['customer'] = $customer;
    // pesapal auth
    $authkey = getAuthKey($logger);
    // submit order request
    curl_setopt($curl, CURLOPT_URL, "https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json', 
        "Authorization: Bearer $authkey"
    ]);

    $name_parts = explode(" ", $_POST['fullname']);
    $body = [
        "id" => $_POST['merchant_id'],
        "currency" => $_SESSION['localle']['country']['currency'],
        "amount" => $_SESSION['locallePrice'],
        "description" => "Payment for Managex software plan: " . $_SESSION['plan']['name'],
        "callback_url" => "http://localhost:3000/managex/controllers/process_payment.php",
        "cancellation_url" => "http://localhost:3000/new-kingsoft?cancel=true",
        "notification_id" => "7a029074-90e0-460e-aa87-dd9b2ae0ffd2",
        "billing_address" => array_filter([
            "email_address" => $_POST['email'],
            "phone_number" => $_POST['phone'] ?? NULL,
            "first_name" => $name_parts[0],
            "last_name" => end($name_parts),
            "line_1" => $_POST['address'] ?? NULL,
            "country_code" => $_SESSION['localle']['country']['iso_code'],
            "city" => $_POST['city'] ?? NULL,
            "state" => $_POST['state'] ?? NULL,
            "postal_code" => $_POST['postal_code'] ?? NULL,
        ], function($value) {
            return !is_null($value);
        })
    ];
    if($_SESSION['plan']['payment_frequency'] !== 'ONETIME') {
        $_SESSION['plan']['expiry'] = $_POST['end_date'];
        $startDate = new DateTime($_POST['start_date']);
        $endDate = new DateTime($_POST['end_date']);

        $body = array_merge($body, [
            'account_number' => $customer['id'],
            'subscription_details' => [
                'start_date' => $startDate->format('d-m-Y'),
                'end_date' => $endDate->format('d-m-Y'),
                'frequency' => $_SESSION['plan']['payment_frequency']
            ]
        ]);
    }
    
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
    $res = json_decode(curl_exec($curl), true);
    if(!$res || !empty(curl_error($curl))) {
        throw new Exception("Error submitting pesapal order request: " . stripslashes(curl_error($curl)), curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
    }
    if($res['status'] != 200) {
        throw new Exception("Error submitting pesapal order request: " . $res['error']['code'], (int)$res['status']);
    }
    $pdo_conn->commit();
    $_SESSION['step'] = 2;
    $_SESSION['redirectUrl'] = $res['redirect_url'];
    echo json_encode($res);
} catch (Exception $e) {
    $pdo_conn->rollBack();
    $apiLogger->critical("Failed to complete order request", ["message" => $e->getMessage()]);
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
} finally {
    if($pdo_conn->inTransaction()) {
        $pdo_conn->rollBack();
    }
    curl_close($curl);
}