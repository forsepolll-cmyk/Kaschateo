<?php
require_once __DIR__ . '/../config.php';

$errors = [];

// Если уже авторизован, редирект на главную
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF токена
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Недействительный токен безопасности';
    } else {
        $login = sanitizeInput($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($login) || empty($password)) {
            $errors[] = 'Заполните все поля';
        } else {
            // Поиск пользователя по email или username
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR username = ?) AND is_active = 1");
            $stmt->execute([$login, $login]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Успешный вход
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                // Обновление времени последнего входа
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                // Логирование
                logLoginAttempt($pdo, $user['id'], true);
                
                // Редирект
                $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirect");
                exit;
            } else {
                $errors[] = 'Неверный логин или пароль';
                
                // Логирование неудачной попытки
                if ($user) {
                    logLoginAttempt($pdo, $user['id'], false);
                }
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
    <title>Вход - Кашатео</title>
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
            max-width: 500px;
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
            margin-bottom: 25px;
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
            <a href="register.php">📝 Регистрация</a>
        </div>
    </nav>
    
    <main class="auth-page">
        <div class="auth-container">
            
            
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon">🐾</div>
                    <h2 class="auth-title">Вход в аккаунт</h2>
                    <p class="auth-subtitle">Добро пожаловать обратно!</p>
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
                        <label for="login">Email или имя пользователя</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input type="text" id="login" name="login" 
                                   value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                                   required placeholder="Введите email или логин">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" 
                                   required placeholder="Введите пароль">
                        </div>
                    </div>
                    
                    <button type="submit" class="submit-btn">🔐 Войти в аккаунт</button>
                </form>
                
                <div class="auth-footer">
                    <p>Нет аккаунта? <a href="register.php" class="auth-link">Зарегистрироваться</a></p>
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