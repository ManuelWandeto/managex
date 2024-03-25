<?php
use Monolog\Logger;
require_once("pesapal_auth.php");
function getTransactionStatus(string $trackingId, Logger $logger) {
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
        return $res;
    } catch (Exception $e) {
        $logger->critical("Failed to get transaction status", ["tracking_id" => $trackingId, "message" => $e->getMessage()]);
        throw $e;
    } finally {
        curl_close($curl);
    }
}