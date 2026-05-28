<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кашатео - Лучший зоомагазин в Ярославле</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .main-banner {
            width: 100%;
            max-width: 1000px;
            display: block;
            margin: 20px auto 40px auto;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .categories-title {
            text-align: center;
            margin: 40px 0 10px;
            font-size: 2rem;
            color: #004d40;
        }
        
        .categories-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 900px;
            margin: 0 auto 50px auto;
        }
        
        .category-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-decoration: none;
            display: block;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .category-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        
        .category-name {
            padding: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            background: white;
            color: #00695c;
        }
        
        .category-name .emoji {
            font-size: 1.3rem;
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                padding: 0 15px;
            }
            .category-image {
                height: 150px;
            }
            .categories-title {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }
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
    
    <main>
        <div style="text-align: center; background: linear-gradient(135deg, #e0f7fa, #b2dfdb); padding: 30px 0 20px 0;">
            <img src="images/catdog.png" alt="Кошки и собаки" class="main-banner" onerror="this.style.display='none'">
        </div>
        
        <div class="container">
            <h2 class="categories-title">Виды животных</h2>
            <p class="categories-subtitle">Выберите нужного питомца</p>
            
            <div class="categories-grid">
                <a href="catalog.php?category=1" class="category-card">
                    <img src="images/cat.jpg" alt="Кошки" class="category-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23e0f2f1%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22%3E🐱%3C/text%3E%3C/svg%3E'">
                    <div class="category-name">Кошки</div>
                </a>
                <a href="catalog.php?category=2" class="category-card">
                    <img src="images/dog.jpeg" alt="Собаки" class="category-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23e0f2f1%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22%3E🐶%3C/text%3E%3C/svg%3E'">
                    <div class="category-name">Собаки</div>
                </a>
                <a href="catalog.php?category=3" class="category-card">
                    <img src="images/hamster.avif" alt="Грызуны" class="category-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23e0f2f1%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22%3E🐹%3C/text%3E%3C/svg%3E'">
                    <div class="category-name">Грызуны</div>
                </a>
                <a href="catalog.php?category=5" class="category-card">
                    <img src="images/fish.jpg" alt="Рыбы" class="category-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23e0f2f1%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22%3E🐠%3C/text%3E%3C/svg%3E'">
                    <div class="category-name">Рыбы</div>
                </a>
                <a href="catalog.php?category=8" class="category-card">
                    <img src="images/spider.jpg" alt="Насекомые" class="category-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23e0f2f1%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22%3E🕷️%3C/text%3E%3C/svg%3E'">
                    <div class="category-name">Насекомые</div>
                </a>
                <a href="catalog.php?category=4" class="category-card">
                    <img src="images/bird.jpg" alt="Птицы" class="category-image" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Crect width=%22100%22 height=%22100%22 fill=%22%23e0f2f1%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2240%22%3E🐦%3C/text%3E%3C/svg%3E'">
                    <div class="category-name">Птицы</div>
                </a>
            </div>
        </div>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>🐾 Кашатео - Лучший зоомагазин в Ярославле</p>
            <p>&copy; 2026 Все права защищены</p>
        </div>
    </footer>
</body>
</html>