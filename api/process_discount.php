<?php

require_once(dirname(__FILE__) .'/../db/db.inc.php');
require_once(dirname(__FILE__) .'/../db/queries.inc.php');
require_once(dirname(__FILE__) .'/../utils/logger.php');
require_once(dirname(__FILE__) .'/../utils/respond.php');
$apiLogger->info('Process discount request');

try {
    $sql = 
        "SELECT * FROM discounts WHERE `code` = ?;";
    $stmt = $pdo_conn->prepare($sql);
    $stmt->execute([
        $_GET['code']
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$result) {
        throw new Exception("No such discount found!", 404);
    }
    // check if expired
    $expiry = new DateTime($result['expiry']);
    if($expiry < new DateTime()) {
        throw new Exception("This discount has expired!", 400);
    }
    $discount = [
        "valid" => true,
        "discount_id" => $result['id'],
        "code" => $result['code'],
        "fraction" => $result['fraction'],
        "expiry" => $result["expiry"]
    ];

    array_push($_SESSION['discounts'], $discount);
    
    echo json_encode($discount);
    exit;
} catch (PDOException $e) {
    $dbLogger->critical("Error processing discount code", ['code' => $_GET['code'], 'message' => $e->getMessage()]);
    respondWith(500, "Internal error occured processing discount, please try again");
} catch(Exception $e) {
    respondWith(isHtmlStatusCode($e->getCode()) ? $e->getCode() : 500, $e->getMessage());
}