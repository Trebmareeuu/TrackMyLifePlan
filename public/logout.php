<?php
session_start();

if (isset($_COOKIE['remember_me'])) {
    list($selector, $token) = explode(':', $_COOKIE['remember_me']);

    require_once __DIR__ . '/../src/includes/db.php';
    $sql = "DELETE FROM remembered_users WHERE selector = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selector]);

    setcookie('remember_me', '', time() - 3600, '/');
}

$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;
?>
