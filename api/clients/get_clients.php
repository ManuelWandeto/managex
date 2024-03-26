<?php

require_once('../../db/db.inc.php');
require_once('../../db/clients.inc.php');
require_once('../../utils/respond.php');
require_once('../../utils/logger.php');


try {
    $clients = getClients($pdo_conn, $dbLogger);
    if (!$clients) {
        throw new Exception("Uncaught error getting clients", 500);
    }
    echo json_encode($clients);
} catch (Exception $e) {
    respondWith($e->getCode(), $e->getMessage());
}