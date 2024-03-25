<?php
require_once(__DIR__ . '/../utils/logger.php'); 
require_once(__DIR__ . '/../utils/respond.php'); 
require_once(__DIR__ . '/../utils/load_env.php'); 
// PDO connection
$pdo_conn = null;
$email_conn = null;

$dsn = "mysql:host={$_ENV['PROD_DB_HOST']};dbname={$_ENV['PROD_DB_NAME']};charset=UTF8";
$email_dsn = "mysql:host={$_ENV['PROD_EMAIL_DB_HOST']};dbname={$_ENV['PROD_EMAIL_DB_NAME']};port={$_ENV['PROD_EMAIL_DB_PORT']};charset=UTF8";
try {
    $pdo_conn = new PDO($dsn, $_ENV['PROD_DB_USER'], $_ENV['PROD_DB_PASS']);
    $pdo_conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
    $pdo_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        //code...
        $email_conn = new PDO($email_dsn, $_ENV['PROD_EMAIL_DB_USER'], $_ENV['PROD_EMAIL_DB_PASS']);
        $email_conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
        $email_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (Exception $e) {
        $dbLogger->critical('Could not connect to email db', ['message'=>$e->getMessage()]);
    }
    
} catch (PDOException $e) {
    $dbLogger->alert('Could not connect to db', ['message'=>$e->getMessage()]);
    echo "Error connecting to main database: ". $e->getMessage();
    // respondWith(500, 'PDO:Error connecting to DB: ' . $e->getMessage());
}