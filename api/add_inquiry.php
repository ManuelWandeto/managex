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
    $notificationMail = file_get_contents(__DIR__ . '/../email_templates/inquiry_notification.html');
    $parsedMail = str_replace([
        "{name}",
        "{email}",
        "{country_name}",
        "{phone}",
        "{sent_at}",
        "{message}"
    ], [
        !empty($_POST['name']) ? $_POST['name'] : "Blank",
        !empty($_POST['email']) ? $_POST['email'] : "Blank",
        !empty($_SESSION['localle']) ? $_SESSION['localle']['country']['name'] : "N/A",
        !empty($_POST['phone']) ? $_POST['phone'] : "Blank",
        date("Y-m-d \\a\\t g:ia", strtotime($inquiry['created_at'])),
        $_POST['message']
    ], $notificationMail);

    foreach (["customercare@kingsoft.biz", "info@kingsoft.biz"] as $email) {
        $sentmail = send_mail($email_conn, [
            "to" => $email,
            "about" => "New Inquiry Notification",
            "message" => $parsedMail
        ], $dbLogger);
    }
    
    echo json_encode($inquiry);
    exit;
} catch (PDOException $e) {
    $dbLogger->critical("Error making inquiry", ['message' => $e->getMessage()]);
    respondWith(500, "Internal error occured making inquiry, please try again");
} catch(Exception $e) {
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}