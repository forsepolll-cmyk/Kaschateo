<?php
require_once __DIR__ . '/../config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF токена
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Недействительный токен безопасности';
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = sanitizeInput($_POST['first_name'] ?? '');
        $last_name = sanitizeInput($_POST['last_name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        
        // Валидация
        if (empty($username)) {
            $errors[] = 'Имя пользователя обязательно';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Имя пользователя должно быть не менее 3 символов';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Имя пользователя может содержать только буквы, цифры и подчеркивание';
        }
        
        if (empty($email)) {
            $errors[] = 'Email обязателен';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Неверный формат email';
        }
        
        if (empty($password)) {
            $errors[] = 'Пароль обязателен';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Пароль должен быть не менее 6 символов';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Пароли не совпадают';
        }
        
        // Проверка уникальности
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Пользователь с таким именем или email уже существует';
            }
        }
        
        // Создание пользователя
        if (empty($errors)) {
            $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'user')");
            
            if ($stmt->execute([$username, $email, $password_hash, $first_name, $last_name, $phone])) {
                // Автоматический вход после регистрации
                $userId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['user_role'] = 'user';
                
                // Логирование
                logLoginAttempt($pdo, $userId, true);
                
                // Редирект на главную
                header('Location: ../index.php');
                exit;
            } else {
                $errors[] = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Кашатео</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e0f7fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header {
            background: linear-gradient(135deg, #00695c, #004d40);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2rem;
        }

        .header .slogan {
            color: #b2dfdb;
            font-size: 0.9rem;
        }

        .nav {
            background-color: #004d40;
            padding: 12px 0;
        }

        .nav a {
            color: white;
            text-decoration: none;
            margin: 0 20px;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .nav a:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        .auth-page {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #e0f7fa 0%, #b2dfdb 100%);
        }

        .auth-container {
            width: 100%;
            max-width: 600px;
        }

        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .auth-title {
            font-size: 2rem;
            color: #004d40;
            margin-bottom: 10px;
        }

        .auth-subtitle {
            color: #666;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #004d40;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #999;
        }

        .form-group input {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #b2dfdb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #f9f9f9;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00695c;
            background: white;
            box-shadow: 0 0 0 3px rgba(0,105,92,0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row .form-group input {
            padding-left: 15px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00695c, #004d40);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,105,92,0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #e53935;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .password-hint {
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .auth-footer p {
            color: #666;
            margin-bottom: 10px;
        }

        .auth-link {
            color: #00695c;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .back-link {
            display: inline-block;
            color: #00695c;
            text-decoration: none;
            font-size: 0.95rem;
            margin-top: 15px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .pets-decoration {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .footer {
            background-color: #004d40;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>🐾 Кашатео</h1>
            <p class="slogan">Лучший зоомагазин в Ярославле</p>
        </div>
    </header>
    
    <nav class="nav">
        <div class="container">
            <a href="../index.php">🏠 Главная</a>
            <a href="../catalog.php?type=products">📦 Каталог</a>
            <a href="../cart.php">🛒 Корзина</a>
            <a href="../contacts.php">📞 Контакты</a>
            <a href="login.php">🔐 Войти</a>
        </div>
    </nav>
    
    <main class="auth-page">
        <div class="auth-container">
            
            
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon">🎉</div>
                    <h2 class="auth-title">Регистрация</h2>
                    <p class="auth-subtitle">Присоединяйтесь к нашему сообществу любителей животных!</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <div>❌ <?= $error ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label for="username">Имя пользователя *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input type="text" id="username" name="username" 
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   required minlength="3" pattern="[a-zA-Z0-9_]+"
                                   placeholder="Придумайте имя пользователя">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required placeholder="your@email.com">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Имя</label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                   placeholder="Ваше имя">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Фамилия</label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                   placeholder="Ваша фамилия">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Телефон</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📱</span>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                   placeholder="+7 (___) ___-__-__">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" 
                                   required minlength="6"
                                   placeholder="Минимум 6 символов">
                        </div>
                        <div class="password-hint">🔸 Минимум 6 символов</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Подтвердите пароль *</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   required placeholder="Повторите пароль">
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">📝 Создать аккаунт</button>
                </form>
                
                <div class="auth-footer">
                    <p>Уже есть аккаунт? <a href="login.php" class="auth-link">Войти</a></p>
                    <a href="../index.php" class="back-link">← Вернуться на главную</a>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>🐾 Кашатео - Лучший зоомагазин в Ярославле</p>
        </div>
    </footer>
</body>
</html>