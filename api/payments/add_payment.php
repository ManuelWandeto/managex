<?php

require_once(__DIR__ . '/../../db/db.inc.php');
require_once(__DIR__ . '/../../db/queries.inc.php');
require_once(__DIR__ . '/../../utils/respond.php');
require_once(__DIR__ . '/../../utils/logger.php');
require_once(__DIR__ . '/../../utils/currency_convert.php');

$apiLogger->info('Add payment request');

try {
    $payment = add_payment($pdo_conn, $_POST, $dbLogger);
    if (!$payment) {
        throw new Exception("UNCAUGHT error adding payment", 500);
    }
    $confirmationMail = file_get_contents(__DIR__ . '/../../email_templates/payment_verification.html');
    $parsedMail = str_replace([
        "{customer_name}",
        "{paid_amount}",
        "{confirmation_code}",
    ], [
        $payment['customer_name'],
        format_money($payment['currency'], "en_KE", $payment['paid_amount']),
        $payment['confirmation_code'],
        format_money("KES", "en_KE", $payment['paid_amount'])
    ], $confirmationMail);

    $sentmail = send_mail($email_conn, [
        "to" => $payment['customer_mail'],
        "about" => "Payment Verified",
        "message" => $parsedMail
    ], $dbLogger);
    if(!$sentmail) {
        $dbLogger->critical("Error sending payment confirmation mail", ['customer_mail' => $payment['customer_mail']]);
    }
    echo json_encode($payment);
} catch (Exception $e) {
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}