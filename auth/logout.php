<?php
require_once __DIR__ . '/../config.php';

// Удаление remember token
if (isset($_COOKIE['remember_token'])) {
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->execute([$_COOKIE['remember_token']]);
    setcookie('remember_token', '', time() - 3600, '/');
}

// Очистка сессии
$_SESSION = [];
session_destroy();

// Редирект на главную
header('Location: ../index.php?message=logged_out');
exit;
?>