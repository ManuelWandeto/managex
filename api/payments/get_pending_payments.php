<?php

require_once(__DIR__ . '/../../db/db.inc.php');
require_once(__DIR__ . '/../../db/queries.inc.php');

require_once('../../utils/respond.php');
require_once('../../utils/logger.php');


try {
    $payments = get_pending_codes($pdo_conn, $dbLogger);
    echo json_encode($payments);
} catch (Exception $e) {
    respondWith($e->getCode(), $e->getMessage());
}