<?php

require_once(__DIR__ . '/../../db/db.inc.php');
require_once(__DIR__ . '/../../db/reports.inc.php');

require_once('../../utils/respond.php');
require_once('../../utils/logger.php');


try {
    $stats = getDownloadStats($pdo_conn, $dbLogger);

    echo json_encode($stats);
} catch (Exception $e) {
    respondWith(500, $e->getMessage());
}