<?php

require_once('../../db/db.inc.php');
require_once('../../db/clients.inc.php');
require_once('../../utils/respond.php');
require_once('../../utils/logger.php');


try {
    $clients = getClients($pdo_conn, $dbLogger);

    echo json_encode($clients);
} catch (Exception $e) {
    respondWith($e->getCode(), $e->getMessage());
}