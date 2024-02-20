<?php

require_once(__DIR__ ."/../vendor/autoload.php");

use Monolog\Logger;

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

function getIpLocalle(Logger $dbLogger) {
    $curl = curl_init();
    try {
        $key = $_ENV['GEOAPIFY_KEY'];
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.geoapify.com/v1/ipinfo?apiKey=$key",
          CURLOPT_RETURNTRANSFER => true,
        ));
        
        $response = curl_exec($curl);
        if($response === false) {
          throw new Exception(stripslashes(curl_error($curl)), 500);
        }
        return json_decode($response, true);
      } catch (Exception $e) {
        $dbLogger->error("Error getting IP localle data", ['message' => $e->getMessage()]);
        throw $e;
      } finally {
        curl_close($curl);
      }
}
