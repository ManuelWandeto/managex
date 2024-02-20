<?php
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../db/queries.inc.php');
require_once(__DIR__ . '/../utils/pesapal_auth.php');
require_once(__DIR__ . '/../utils/redirect.php');
require_once(__DIR__ . '/../utils/redirect.php');
require_once(__DIR__ . '/../utils/logger.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$logger->withName('Controller')->info('Process payment request', ['customer' => $_SESSION['customer']['id'], 'plan' => $_SESSION['plan']['id']]);

$trackingId = $_GET['OrderTrackingId'];

$curl = curl_init();
try {
    $authkey = getAuthKey($logger);
    // get transaction status
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://cybqa.pesapal.com/pesapalv3/api/Transactions/GetTransactionStatus?orderTrackingId=$trackingId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            "Authorization: Bearer $authkey"
        ]
    ]);
    $res = json_decode(curl_exec($curl), true);
    if(!$res || !empty(curl_error($curl))) {
        throw new Exception("Error getting transation status: " . stripslashes(curl_error($curl)), curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
    }
    if($res['status'] != 200) {
        throw new Exception("Error getting transation status: " . $res['error']['code'], (int)$res['status']);
    }
    // enroll customer to plan paid for, send confirmation email with download link and redirect back to checkout page on step 3 if success
    $status = $res['status_code'];
    $_SESSION['checkout_response'] = $res;
    if($status === 1) {
        $_SESSION['step'] = 3;
        // try {
            //TODO: if enroll_customer fails, retry atleast 3 times
        // } catch (Exception $e) {
        //     //throw $th;
        // }
        $enrollment = enroll_customer($pdo_conn, [
            'plan_id' => $_SESSION['plan']['id'],
            'customer_id' => $_SESSION['customer']['id'],
            'expiry' => $_SESSION['plan']['payment_frequency'] !== 'ONETIME' ? $_SESSION['plan']['expiry'] : NULL,
            'amount' => $res['amount']
        ], $dbLogger);
        if(!$enrollment) {
            throw new Exception("Failed to enroll a paid user!", 500);
        }
    }
    redirect("http://localhost:3000/managex/checkout.php?plan=1");
} catch (Exception $e) {
    $apiLogger->withName('Controller')->critical("Failed to get transaction status", ['customer' => $_SESSION['customer']['id'], 'plan' => $_SESSION['plan']['id'], 'message' => $e->getMessage()]);
    redirect("http://localhost:3000/managex/checkout.php?plan=1&status=error");

} finally {
    curl_close($curl);
}