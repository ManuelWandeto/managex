<?php

require_once(dirname(__FILE__) .'/../db/db.inc.php');
require_once(dirname(__FILE__) .'/../db/queries.inc.php');
require_once(dirname(__FILE__) .'/../utils/logger.php');
require_once(dirname(__FILE__) .'/../utils/respond.php');
$apiLogger->info('Visitor inquiry request');

try {
    $sql = 
        "INSERT INTO inquiries (customer_name, customer_phone, email, `message`) VALUES (?, ?, ?, ?);";
    $stmt = $pdo_conn->prepare($sql);
    $stmt->execute([
        !empty($_POST['name']) ? $_POST['name'] : NULL,
        !empty($_POST['phone']) ? $_POST['phone'] : NULL,
        !empty($_POST['email']) ? $_POST['email'] : NULL,
        $_POST['message']
    ]);

    $id = $pdo_conn->lastInsertId();
    $inquiry = $pdo_conn->query("SELECT * FROM inquiries WHERE id = $id;")->fetch(PDO::FETCH_ASSOC);
    if(!$inquiry) {
        throw new Exception("UNCAUGHT error making inquiry", 500);
    }
    echo json_encode($inquiry);
    exit;
} catch (PDOException $e) {
    $dbLogger->critical("Error making inquiry", ['message' => $e->getMessage()]);
    respondWith(500, "Internal error occured making inquiry, please try again");
} catch(Exception $e) {
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}