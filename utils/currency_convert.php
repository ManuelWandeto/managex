<?php

require_once(__DIR__ ."/../vendor/autoload.php");
use Monolog\Logger;

function convert_currrency(string $currency, float $amount, Logger $dbLogger) {
    $curl = curl_init();
    try {
        $currency = strtolower($currency);
        curl_setopt($curl, CURLOPT_URL, "https://cdn.jsdelivr.net/gh/fawazahmed0/currency-api@1/latest/currencies/$currency/kes.json");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        
        $response = curl_exec($curl);
        if($response === false) {
            throw new Exception(stripslashes(curl_error($curl)), 500);
        }
        $json = json_decode($response, true);

        return $amount / $json['kes'];
    } catch (Exception $e) {
        $dbLogger->error("Error getting exchange rate data", ['message' => $e->getMessage()]);
        throw $e;
    } finally {
        curl_close($curl);
    }
}

