<?php

require_once(__DIR__ ."/../vendor/autoload.php");
use Monolog\Logger;

function convert_currrency(string $currency, float $amount, Logger $logger) {
    $curl = curl_init();
    try {
        $currency = strtolower($currency);
        if($currency == 'kes') {
            return $amount;
        }
        curl_setopt($curl, CURLOPT_URL, "https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/kes.json");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
        
        $response = curl_exec($curl);
        if(!$response || !empty(curl_error($curl))) {
            throw new Exception("Error fetching currency rate: " . stripslashes(curl_error($curl)), curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
        }
        $json = json_decode($response, true);
        return round($amount * $json['kes']['usd'], 0, PHP_ROUND_HALF_EVEN);
    } catch (Exception $e) {
        $logger->withName("Currency Converter")->error("Error getting exchange rate data", ['message' => $e->getMessage()]);
        throw $e;
    } finally {
        curl_close($curl);
    }
}

function format_money(string $currency, string $localle, float $amount) {
    $formatter = new NumberFormatter($localle, NumberFormatter::CURRENCY);
    return $formatter->formatCurrency($amount, $currency);
}