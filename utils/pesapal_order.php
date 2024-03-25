<?php
use Monolog\Logger;
require_once('pesapal_auth.php');
function submitOrderRequest(array $requestBody, Logger $logger) {
    $curl = curl_init();
    try {
        //code...
        $logger = $logger->withName('PESAPAL');
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
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($requestBody));
        $res = json_decode(curl_exec($curl), true);
        if(!$res || !empty(curl_error($curl))) {
            throw new Exception("Error submitting retry pesapal order request: " . stripslashes(curl_error($curl)), curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
        }
        if($res['status'] != 200) {
            throw new Exception("Error submitting retry pesapal order request: " . $res['error']['code'], (int)$res['status']);
        }
        return $res;
    } catch (Exception $e) {
        $logger->critical("Error submitting order request", ['message' => $e->getMessage()]);
        throw $e;
    } finally {
        curl_close($curl);
    }
}