<?php

require_once('../../db/db.inc.php');
require_once('../../utils/redirect.php');

if(!isset($_POST['submit'])) {
    redirect('login.php?error=unauthorised+route');
}
$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo_conn->prepare("SELECT * FROM users WHERE username = ?;");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$user) {
    redirect('login.php?error=no+such+user');
}
if(!password_verify($password, $user['hash_pwd'])) {
    redirect('login.php?error=incorrect+password');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
redirect('../index.php');