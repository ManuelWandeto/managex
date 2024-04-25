<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../utils/redirect.php");
require_once("../utils/logger.php");
require_once("../utils/respond.php");
require_once("../db/db.inc.php");
require_once("../db/queries.inc.php");

$apiLogger->info("Update download status request");

try {
    $download_id = $_POST['download_id'];

    if(!$download_id) {
        throw new Exception("No download id supplied", 400);
    }
    $defaultVersion = $pdo_conn->query("SELECT * FROM versions WHERE is_default = 1 ORDER BY creation_date DESC LIMIT 1;")->fetch(PDO::FETCH_ASSOC);
    
    $sql = 
        "UPDATE downloads SET `status` = ?, version_id = ? WHERE id = ?;";

    $stmt = $pdo_conn->prepare($sql);
    $stmt->execute([
        'COMPLETED',
        $defaultVersion['id'],
        $download_id
    ]);

    $download = $pdo_conn->query(
        "
        SELECT 
            d.id AS id,
            d.`is_paid`,
            d.`country_name`,
            c.`business_name`,
            c.`email` AS business_mail,
            d.`status`,
            v.`full_name` AS version,
            d.`created_at`
        FROM downloads d 
        JOIN customers c ON d.`customer` = c.`id`
        JOIN versions v ON d.`version_id` = v.`id`
        WHERE d.id = $download_id;
        "
    )->fetch(PDO::FETCH_ASSOC);
    
    $notificationMail = file_get_contents(__DIR__ . '/../email_templates/download_notification.html');
    $parsedMail = str_replace([
        "{download_type}",
        "{customer_name}",
        "{customer_mail}",
        "{country_name}",
        "{version}",
        "{download_time}"
    ], [
        (bool)$download['is_paid'] ? "paid" : "free",
        $download['business_name'],
        $download['business_mail'],
        $download['country_name'],
        $download['version'],
        date("Y-m-d \\a\\t g:ia", strtotime($download['created_at']))
    ], $notificationMail);

    foreach (["customercare@kingsoft.biz", "info@kingsoft.biz"] as $email) {
        $sentmail = send_mail($email_conn, [
            "to" => $email,
            "about" => "New Download Notification",
            "message" => $parsedMail
        ], $dbLogger);
    }

    if(!$sentmail) {
        $dbLogger->error("Error sending download notification mail");
    }

    echo json_encode($download);
} catch (PDOException $e) {
    $dbLogger->error("Db error updating download status", ['message' => $e->getMessage()]);
    throw $e;
} catch (Exception $e) {
    $apiLogger->error("Error updating download status", ['message' => $e->getMessage()]);
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}