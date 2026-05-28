<?php
$host = 'localhost';
$dbname = 'pet_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Инициализация корзины
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Функции аутентификации
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function hasRole($pdo, $role) {
    $user = getCurrentUser($pdo);
    if (!$user) return false;
    
    if ($role == 'admin') return $user['role'] == 'admin';
    if ($role == 'manager') return in_array($user['role'], ['admin', 'manager']);
    if ($role == 'user') return true;
    return $user['role'] == $role;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: auth/login.php');
        exit;
    }
}

function requireRole($pdo, $role) {
    requireLogin();
    if (!hasRole($pdo, $role)) {
        header('Location: ../index.php?error=access_denied');
        exit;
    }
}

// Функции безопасности
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Функция логирования попыток входа
function logLoginAttempt($pdo, $userId, $successful = true) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $pdo->prepare("INSERT INTO login_logs (user_id, ip_address, user_agent, is_successful) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $ip, $userAgent, $successful ? 1 : 0]);
}
?>