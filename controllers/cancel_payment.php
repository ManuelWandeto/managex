<?php
require_once(__DIR__ . '/../db/db.inc.php');
require_once(__DIR__ . '/../utils/logger.php');
require_once(__DIR__ . '/../utils/redirect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if($_SESSION['order']) {
    $pdo_conn->query("DELETE FROM orders WHERE id = {$_SESSION['order']['id']};");
}
$logger->withName('Controller')->info('Cancel payment request');
session_unset();
session_destroy();
redirect('../index.php');