<?php
require_once 'config.php';

$type = $_GET['type'] ?? 'product';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($type == 'pet') {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM pets p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    $item['type'] = 'pet';
    $stock_field = 'in_stock';
} else {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    $item['type'] = 'product';
    $stock_field = 'stock_quantity';
}

if (!$item) { header('Location: catalog.php'); exit; }

function getProductImage($productName, $categoryId) {
    $images = [
        'Royal Canin для кошек' => 'Royal Canin для кошек.jpeg',
        'Корм для кошек Royal Canin Adult' => 'кошачий корм.png',
        'Влажный корм для кошек' => 'Влажный корм для кошек.jpeg',
        'Наполнитель для кошачьего туалета' => 'наполнитель.webp',
        'Когтеточка-домик для кошек' => 'когтеточка.jpeg',
        'Когтеточка' => 'Когтеточка.jpg',
        'Витамины для кошек' => 'Витамины для кошек.jpg',
        'кошачий корм' => 'кошачий корм.png',
        'Pedigree для собак' => 'Pedigree для собак.jpeg',
        'Корм для собак Purina Pro Plan' => 'корм для собак.jpeg',
        'Поводок-рулетка для собак' => 'поводок.jpg',
        'Игрушка-мяч для собак' => 'мячик.jpg',
        'Игрушка-пищалка "Кость"' => 'Игрушка-пищалка.jpg',
        'Лакомство для собак' => 'Лакомство для собак.jpeg',
        'Витамины для собак' => 'Витамины для собак.jpg',
        'Когтерезка для собак' => 'Когтерезка для собак.jpg',
        'Корм для грызунов Vitakraft' => 'Корм для грызунов Vitakraft.jpg',
        'Колесо для бега' => 'юговое колесо.jpg',
        'Домик для грызунов' => 'домик для гразунов.jpg',
        'Клетка для грызунов' => 'Клетка для грызунов.jpg',
        'Поилка для грызунов' => 'Поилка для грызунов.jpeg',
        'Корм для попугаев' => 'Корм для попугаев.jpg',
        'Клетка для птиц' => 'Клетка для птиц.webp',
        'Игрушка-качели для птиц' => 'Игрушка-качели для птиц.jpeg',
        'Корм для рыб Tetra' => 'Корм для рыб Tetra.jpeg',
        'Аквариумный фильтр' => 'Аквариумный фильтр.jpg',
        'Обогреватель для аквариума' => 'Обогреватель для аквариума.jpg',
        'Террариум для пауков' => 'Террариум для пауков.jpg',
        'Субстрат для насекомых' => 'Субстрат для насекомых.jpg',
        'Кормовые насекомые' => 'Кормовые насекомые.jpg',
        'Переноска для животных' => 'Переноска для животных.jpg',
        'Средство от блох для кошек' => 'Средство от блох для кошек.jpg',
        'Глистогонное для собак' => 'Глистогонное для собак.jpg',
        'Капли для глаз' => 'Капли для глаз.webp',
    ];
    if (isset($images[$productName])) return 'images/' . $images[$productName];
    foreach ($images as $key => $img) if (strpos($productName, $key) !== false) return 'images/' . $img;
    if ($categoryId == 1) return 'images/cat.jpg';
    if ($categoryId == 2) return 'images/dog.jpeg';
    return 'images/catdog.png';
}

function getPetImage($petName, $categoryId) {
    $petImages = ['Барсик' => 'Барсик.jpg', 'Мурка' => 'Мурка.jpg', 'Рекс' => 'Рекс.jpg', 'Лайма' => 'Лайма.jpg', 'Хома' => 'Хома.jpg', 'Кеша' => 'Кеша.jpeg'];
    if (isset($petImages[$petName])) return 'images/' . $petImages[$petName];
    if ($categoryId == 1) return 'images/cat.jpg';
    if ($categoryId == 2) return 'images/dog.jpeg';
    return 'images/catdog.png';
}

$image_path = $type == 'pet' ? getPetImage($item['name'], $item['category_id']) : getProductImage($item['name'], $item['category_id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item['name']) ?> - Кашатео</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .product-page { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .product-detail { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: white; border-radius: 20px; padding: 30px; margin: 20px 0; }
        .product-image { background: linear-gradient(135deg, #e0f2f1, #b2dfdb); border-radius: 16px; height: 400px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; }
        .product-info h1 { font-size: 1.8rem; color: #004d40; margin-bottom: 15px; }
        .product-category { display: inline-block; background: #e0f2f1; color: #00695c; padding: 5px 12px; border-radius: 20px; margin-bottom: 15px; }
        .product-brand { color: #666; margin-bottom: 10px; }
        .price-quantity-row { display: flex; justify-content: space-between; align-items: center; margin: 25px 0; padding: 15px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; }
        .product-price { font-size: 2rem; font-weight: bold; color: #00695c; }
        .quantity-control { display: flex; align-items: center; gap: 12px; }
        .quantity-control input { width: 70px; padding: 10px; text-align: center; border: 1px solid #ddd; border-radius: 8px; }
        .cart-button-wrapper { display: flex; justify-content: center; margin: 25px 0; }
        .btn-cart { padding: 14px 50px; background: #00695c; color: white; border: none; border-radius: 40px; font-size: 1.1rem; font-weight: 600; cursor: pointer; min-width: 250px; }
        .btn-cart:hover { background: #004d40; }
        .in-stock { display: inline-block; padding: 8px 15px; background: #e8f5e9; border-radius: 8px; color: #2e7d32; }
        .back-link { display: inline-block; margin: 20px 0; color: #00695c; text-decoration: none; }
        @media (max-width: 768px) { .product-detail { grid-template-columns: 1fr; } .product-image { height: 250px; } .price-quantity-row { flex-direction: column; gap: 15px; align-items: flex-start; } .btn-cart { width: 100%; } }
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
    <main class="product-page">
        <a href="catalog.php?type=<?= $type ?>" class="back-link">← Назад в каталог</a>
        <div class="product-detail">
            <div class="product-image"><img src="<?= $image_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.parentElement.innerHTML='<div style=\'font-size:4rem\'>' + (<?= $type == 'pet' ? '\'🐕\'' : '\'📦\'' ?> ) + '</div>'"></div>
            <div class="product-info">
                <span class="product-category"><?= htmlspecialchars($item['category_name'] ?? 'Товар') ?></span>
                <h1><?= htmlspecialchars($item['name']) ?></h1>
                <?php if (isset($item['brand']) && $item['brand']): ?><div class="product-brand">Бренд: <?= htmlspecialchars($item['brand']) ?></div><?php endif; ?>
                <?php if ($type == 'pet'): ?>
                    <div class="product-brand">Порода: <?= htmlspecialchars($item['breed'] ?? 'Не указана') ?></div>
                    <div class="product-brand">Возраст: <?= ($item['age_months'] ?? 0) ?> мес.</div>
                    <div class="product-brand">Пол: <?= $item['gender'] == 'male' ? 'Мальчик' : ($item['gender'] == 'female' ? 'Девочка' : 'Неизвестно') ?></div>
                    <div class="product-brand">Окрас: <?= htmlspecialchars($item['color'] ?? 'Не указан') ?></div>
                <?php endif; ?>
                <div class="price-quantity-row">
                    <div class="product-price"><?= number_format($item['price'], 0, '', ' ') ?> ₽</div>
                    <div class="quantity-control"><label>Количество:</label><input type="number" id="quantity" value="1" min="1" max="<?= $item[$stock_field] ?? 10 ?>"><span>шт.</span></div>
                </div>
                <div class="cart-button-wrapper"><button class="btn-cart" onclick="addToCart()">🛒 Добавить в корзину</button></div>
                <div class="in-stock"><?= ($item[$stock_field] ?? 0) > 0 ? "✅ В наличии: {$item[$stock_field]} шт." : '❌ Нет в наличии' ?></div>
            </div>
        </div>
    </main>
    <footer class="footer"><div class="container"><p>🐾 Кашатео - Лучший зоомагазин в Ярославле</p></div></footer>
    <script>
        function addToCart() {
            let quantity = document.getElementById('quantity').value;
            window.location.href = `cart.php?action=add&type=<?= $type ?>&id=<?= $item['id'] ?>&quantity=${quantity}`;
        }
    </script>
</body>
</html>