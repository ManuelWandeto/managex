<?php
require_once(__DIR__ . '/../utils/logger.php');
require_once(__DIR__ . '/../utils/redirect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$logger->withName('Controller')->info('Cancel payment request');
session_unset();
session_destroy();
redirect('../index.php');