<?php

require_once('../../db/db.inc.php');
require_once('../../db/clients.inc.php');
require_once('../../utils/respond.php');
require_once('../../utils/logger.php');

$apiLogger->info('Add client request');

try {
    $client = addClient($pdo_conn, $_POST, $dbLogger);
    if (!$client) {
        throw new Exception("Uncaught error adding client", 500);
    }
    echo json_encode($client);
} catch (Exception $e) {
    respondWith($e->getCode(), $e->getMessage());
}