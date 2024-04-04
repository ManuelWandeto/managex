<?php

require_once(__DIR__ . '/../../db/db.inc.php');
require_once(__DIR__ . '/../../db/queries.inc.php');

require_once('../../utils/respond.php');
require_once('../../utils/logger.php');


try {
    $inquiries = $pdo_conn->query("SELECT * FROM inquiries;")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($inquiries);
    exit;
} catch (Exception $e) {
    respondWith($e->getCode(), $e->getMessage());
}