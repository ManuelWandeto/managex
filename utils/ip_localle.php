<?php

require_once(__DIR__ ."/../vendor/autoload.php");

use Monolog\Logger;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();
function getUserIpAddr() {
  $ip = '';
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      // IP from shared internet
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      // IP passed from proxy
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
      // Regular remote address
      $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
}

function getIpLocalle(Logger $logger) {
    $curl = curl_init();
    try {
        $key = $_ENV['GEOAPIFY_KEY'];
        $clientIp = getUserIpAddr();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $clientIp == '127.0.0.1' || $clientIp == '::1' ? "https://api.geoapify.com/v1/ipinfo?apiKey=$key" : "https://api.geoapify.com/v1/ipinfo?ip=$clientIp&apiKey=$key",
          CURLOPT_RETURNTRANSFER => true,
        ));
        
        $response = curl_exec($curl);
        if($response === false) {
          throw new Exception(stripslashes(curl_error($curl)), 500);
        }
        return json_decode($response, true);
      } catch (Exception $e) {
        $logger->withName('IP-LOCALLE')->error("Error getting IP localle data", ['message' => $e->getMessage()]);
        throw $e;
      } finally {
        curl_close($curl);
      }
}
