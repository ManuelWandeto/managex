<?php

require_once('../../db/db.inc.php');
require_once('../../db/clients.inc.php');
require_once('../../utils/respond.php');
require_once('../../utils/logger.php');

$apiLogger->info('Update client request');

try {
    $client = updateClient($pdo_conn, $_POST, $dbLogger);
    echo json_encode($client);
} catch (Exception $e) {
    respondWith($e->getCode() ?? 500, $e->getMessage());
}