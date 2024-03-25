<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once("../utils/redirect.php");
require_once("../db/db.inc.php");

try {
    $download_id = !empty($_GET['download_id']) ? $_GET['download_id'] : NULL;
    if(!$download_id) {
        redirect("https://drive.google.com/uc?export=download&id=1GadlQxYSpK5g8rLNaJJ8lhNnsUe30Hx6");
    }

    $sql = 
        "UPDATE downloads SET `status` = ? WHERE id = ?;";

    $stmt = $pdo_conn->prepare($sql);
    $stmt->execute([
        'COMPLETED',
        $download_id
    ]);

    redirect("https://drive.google.com/uc?export=download&id=1GadlQxYSpK5g8rLNaJJ8lhNnsUe30Hx6");

} catch (Exception $e) {
    $dbLogger->error("Error updating download status", ['message' => $e->getMessage()]);
    throw $e;
}