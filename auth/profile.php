<?php
require_once __DIR__ . '/../config.php';

requireLogin();

$user = getCurrentUser($pdo);
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Недействительный токен безопасности';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $first_name = sanitizeInput($_POST['first_name'] ?? '');
            $last_name = sanitizeInput($_POST['last_name'] ?? '');
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Неверный формат email';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$first_name, $last_name, $phone, $email, $user['id']])) {
                    $success = 'Профиль успешно обновлен';
                    $user = getCurrentUser($pdo);
                }
            }
        } elseif ($action === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (!password_verify($current_password, $user['password_hash'])) {
                $errors[] = 'Неверный текущий пароль';
            } elseif (strlen($new_password) < 6) {
                $errors[] = 'Новый пароль должен быть не менее 6 символов';
            } elseif ($new_password !== $confirm_password) {
                $errors[] = 'Новые пароли не совпадают';
            } else {
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                if ($stmt->execute([$password_hash, $user['id']])) {
                    $success = 'Пароль успешно изменен';
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
    <title>Профиль - Кашатео</title>
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

        .profile-page {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            flex: 1;
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #b2dfdb;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #00695c, #004d40);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 15px;
            border: 4px solid #b2dfdb;
        }
        
        .profile-name {
            font-size: 1.8rem;
            color: #004d40;
            margin: 10px 0;
        }
        
        .profile-username {
            color: #666;
            font-size: 1rem;
            margin-bottom: 10px;
        }
        
        .profile-role {
            display: inline-block;
            background: #e0f2f1;
            color: #00695c;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        .section-title {
            color: #004d40;
            margin: 25px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #b2dfdb;
            font-size: 1.3rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #004d40;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #b2dfdb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #00695c;
            box-shadow: 0 0 0 3px rgba(0,105,92,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #00695c, #004d40);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,105,92,0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
        
        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #e53935;
        }

        .footer {
            background-color: #004d40;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
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
            <a href="logout.php">🚪 Выйти</a>
        </div>
    </nav>
    
    <main class="profile-page">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">🐾</div>
                <h2 class="profile-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
                <div class="profile-username">@<?= htmlspecialchars($user['username']) ?></div>
                <div class="profile-role">
                    <?php 
                    $roles = ['admin' => '👑 Администратор', 'manager' => '👔 Менеджер', 'user' => '👤 Пользователь'];
                    echo $roles[$user['role']] ?? 'Пользователь';
                    ?>
                </div>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= $success ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <div>❌ <?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <h3 class="section-title">📝 Личные данные</h3>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Фамилия</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Телефон</label>
                    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">💾 Сохранить изменения</button>
            </form>
            
            <h3 class="section-title">🔒 Изменить пароль</h3>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label>Текущий пароль</label>
                    <input type="password" name="current_password" required placeholder="Введите текущий пароль">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Новый пароль</label>
                        <input type="password" name="new_password" required minlength="6" placeholder="Минимум 6 символов">
                    </div>
                    <div class="form-group">
                        <label>Подтвердите пароль</label>
                        <input type="password" name="confirm_password" required placeholder="Повторите новый пароль">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">🔄 Изменить пароль</button>
            </form>
        </div>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>🐾 Кашатео - Лучший зоомагазин в Ярославле</p>
        </div>
    </footer>
</body>
</html>