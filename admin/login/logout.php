<?php
require_once('../../utils/redirect.php');
require_once('../../utils/logger.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$apiLogger->info('Admin logout', ['username', $_SESSION['username'], "user_id" => $_SESSION['user_id']]);

session_unset();
session_destroy();
redirect('../index.php');