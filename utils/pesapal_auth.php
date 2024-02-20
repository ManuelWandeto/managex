<?php
require_once(__DIR__ ."/../vendor/autoload.php");
require_once(__DIR__ . '/../db/queries.inc.php');
use Monolog\Logger;
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();
function getAuthKey(Logger $logger) {
    if(!empty($_SESSION['pesapal_auth'])) {
        $expiry = new DateTime($_SESSION['pesapal_auth']['expiryDate']);
        if($expiry > new DateTime()) {
            return $_SESSION['pesapal_auth']['token'];
        }
    }
    $curl = curl_init();
    try {
        curl_setopt($curl, CURLOPT_URL, "https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken");
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            "consumer_key" => $_ENV['CONSUMER_KEY'],
            "consumer_secret" => $_ENV['CONSUMER_SECRET'],
        ]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($curl), true);
        if(!$res || !empty(curl_error($curl))) {
            throw new Exception("Error getting pesapal auth key: " . stripslashes(curl_error($curl)), curl_getinfo($curl, CURLINFO_RESPONSE_CODE));
        }
        if($res['status'] != 200) {
            throw new Exception("Error getting pesapal auth key: " . $res['error']['code'], (int)$res['status']);
        }
        $_SESSION['pesapal_auth'] = $res;
        return $_SESSION['pesapal_auth']['token'];
    } catch (Exception $e) {
        $logger->critical("Failed to get pesapal auth", ["message" => $e->getMessage()]);
        throw new Exception($e->getMessage(), isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500);
    } finally {
        curl_close($curl);
    }
}