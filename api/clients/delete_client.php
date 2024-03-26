<?php

require_once('../../db/db.inc.php');
require_once('../../db/queries/clients.inc.php');
require_once('../../utils/respond.php');
require_once('../../utils/logger.php');

$content = trim(file_get_contents("php://input"));

$decoded = json_decode($content, true);
$apiLogger->info('Delete client request');
  //If json_decode failed, the JSON is invalid.
if( is_array($decoded)) {
    try {
        $ok = deleteClient($conn, $decoded['id'], $dbLogger);
        if (!$ok) {
            throw new Exception("Failed to delete client: ". $decoded['image'], 500);
        }
        echo json_encode($ok);
        exit();
    } catch (Exception $e) {
        respondWith($e->getCode(), $e->getMessage());
    }
} else {
    $apiLogger->error('Invalid json to delete client request');
    respondWith(500, "Invalid json");
}