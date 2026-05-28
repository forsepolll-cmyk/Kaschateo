<?php
require_once 'config.php';
$message_sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Пожалуйста, заполните обязательные поля (Имя, Почта, Сообщение)';
    } else {
        $message_sent = true;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Контакты - Кашатео</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contacts-page { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .page-title { text-align: center; font-size: 2rem; color: #004d40; margin: 30px 0 20px; padding-bottom: 15px; border-bottom: 3px solid #b2dfdb; display: inline-block; }
        .title-wrapper { text-align: center; margin-bottom: 40px; }
        .contacts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin: 30px 0; }
        .contact-info, .contact-form { background: white; padding: 30px; border-radius: 20px; }
        .contact-info h2, .contact-form h2 { text-align: center; color: #00695c; margin-bottom: 25px; }
        .contact-item { text-align: center; margin-bottom: 25px; padding: 12px 0; border-bottom: 1px solid #eee; }
        .contact-label { font-weight: bold; color: #004d40; margin-bottom: 8px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; text-align: center; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; text-align: center; }
        .form-group textarea { text-align: left; }
        .submit-btn { width: 100%; padding: 14px; background: #00695c; color: white; border: none; border-radius: 40px; font-size: 1.1rem; cursor: pointer; }
        .submit-btn:hover { background: #004d40; }
        .success-message, .error-message { text-align: center; padding: 15px; border-radius: 12px; margin-bottom: 20px; }
        .success-message { background: #e8f5e9; color: #2e7d32; }
        .error-message { background: #ffebee; color: #c62828; }
        @media (max-width: 768px) { .contacts-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="header"><div class="container"><h1>🐾 Кашатео</h1><p class="slogan">Лучший зоомагазин в Ярославле</p></div></header>
    <nav class="nav">
    <div class="container">
        <a href="index.php">🏠 Главная</a>
        <a href="catalog.php?type=products">📦 Каталог</a>
        <a href="cart.php">🛒 Корзина</a>
        <a href="contacts.php">📞 Контакты</a>
        <?php if (isLoggedIn()): ?>
            <?php $user = getCurrentUser($pdo); ?>
            <a href="auth/profile.php">👤 <?= htmlspecialchars($user['username']) ?></a>
            <a href="auth/logout.php">🚪 Выйти</a>
        <?php else: ?>
            <a href="auth/login.php">🔐 Войти</a>
            <a href="auth/register.php">📝 Регистрация</a>
        <?php endif; ?>
    </div>
</nav>
    <main class="contacts-page">
        <div class="title-wrapper"><h1 class="page-title">Контакты</h1></div>
        <div class="contacts-grid">
            <div class="contact-info">
                <h2>Свяжитесь с нами</h2>
                <div class="contact-item"><div class="contact-label">EMAIL:</div><div>oooyo@gmail.com<br><small>support@gmail.com - техподдержка</small></div></div>
                <div class="contact-item"><div class="contact-label">Горячая линия:</div><div>+7 (4852) 95-44-05, 95-44-06 - Ярославль<br>+7 (902) 334-44-06 - Москва<br>+7 (902) 334-44-06 - Санкт-Петербург</div></div>
                <div class="contact-item"><div class="contact-label">Адрес:</div><div>г. Ярославль, ул. Зоологическая, д. 15</div></div>
                <div class="contact-item"><div class="contact-label">Режим работы:</div><div>Ежедневно с 9:00 до 21:00</div></div>
            </div>
            <div class="contact-form">
                <h2> Напишите нам</h2>
                <?php if ($message_sent): ?><div class="success-message"> Спасибо! Сообщение отправлено.</div><?php elseif ($error): ?><div class="error-message"> <?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form method="POST">
                    <div class="form-group"><label>Имя *</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Почта *</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Телефон</label><input type="tel" name="phone"></div>
                    <div class="form-group"><label>Тема</label><select name="subject"><option value="question">Вопрос о товаре</option><option value="order">Вопрос о заказе</option><option value="other">Другое</option></select></div>
                    <div class="form-group"><label>Сообщение *</label><textarea name="message" rows="4" required></textarea></div>
                    <button type="submit" class="submit-btn"> Отправить</button>
                </form>
            </div>
        </div>
    </main>
    <footer class="footer"><div class="container"><p>🐾 Кашатео - Лучший зоомагазин в Ярославле</p></div></footer>
</body>
</html>