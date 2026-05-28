<?php
require_once 'config.php';

$type = $_GET['type'] ?? 'products';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$manufacturer = $_GET['manufacturer'] ?? '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : 100000;

$manufacturers = $pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();

if ($type == 'pets') {
    $sql = "SELECT 'pet' as type, p.*, c.name as category_name 
            FROM pets p 
            JOIN categories c ON p.category_id = c.id 
            WHERE p.price BETWEEN :min_price AND :max_price AND p.in_stock > 0";
    $params = [':min_price' => $min_price, ':max_price' => $max_price];
    if ($category_id) {
        $sql .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    $sql .= " ORDER BY p.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} else {
    $sql = "SELECT 'product' as type, p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.price BETWEEN :min_price AND :max_price AND p.stock_quantity > 0";
    $params = [':min_price' => $min_price, ':max_price' => $max_price];
    if ($category_id) {
        $sql .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    if ($manufacturer) {
        $sql .= " AND p.brand = :manufacturer";
        $params[':manufacturer'] = $manufacturer;
    }
    $sql .= " ORDER BY p.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
}

function getProductImage($productName, $categoryId) {
    $images = [
        // Кошки
        'Royal Canin для кошек' => 'Royal Canin для кошек.jpeg',
        'Корм для кошек Royal Canin Adult' => 'кошачий корм.png',
        'Влажный корм для кошек' => 'Влажный корм для кошек.jpeg',
        'Наполнитель для кошачьего туалета' => 'наполнитель.webp',
        'Когтеточка-домик для кошек' => 'когтеточка.jpeg',
        'Витамины для кошек' => 'Витамины для кошек.jpg',
        
        // Собаки
        'Pedigree для собак' => 'Pedigree для собак.jpeg',
        'Корм для собак Purina Pro Plan' => 'корм для собак.jpeg',
        'Поводок-рулетка для собак' => 'поводок.jpg',
        'Игрушка-мяч для собак' => 'мячик.jpg',
        'Игрушка-пищалка "Кость"' => 'Игрушка-пищалка.jpg',
        'Лакомство для собак' => 'Лакомство для собак.jpeg',
        'Витамины для собак' => 'Витамины для собак.jpg',
        'Когтерезка для собак' => 'Когтерезка для собак.jpg',
        
        // Грызуны
        'Корм для грызунов Vitakraft' => 'Корм для грызунов Vitakraft.jpg',
        'Колесо для бега' => 'юеговое колесо.jpg',
        'Домик для грызунов' => 'домик для гразунов.jpg',
        'Клетка для грызунов' => 'Клетка для грызунов.jpg',
        'Поилка для грызунов' => 'Поилка для грызунов.jpeg',
        
        // Птицы
        'Корм для попугаев' => 'Корм для попугаев.jpg',
        'Клетка для птиц' => 'Клетка для птиц.webp',
        'Игрушка-качели для птиц' => 'Игрушка-качели для птиц.jpeg',
        
        // Рыбы
        'Корм для рыб Tetra' => 'Корм для рыб Tetra.jpeg',
        'Аквариумный фильтр' => 'Аквариумный фильтр.jpg',
        'Обогреватель для аквариума' => 'Обогреватель для аквариума.jpg',
        
        // Насекомые
        'Террариум для пауков' => 'Террариум для пауков.jpg',
        'Субстрат для насекомых' => 'Субстрат для насекомых.jpg',
        'Кормовые насекомые' => 'Кормовые насекомые.jpg',
        
        // Корма
        'Корм для собак Chappi Бренд №1' => 'Pedigree для собак.jpg',
        
        // Аксессуары
        'Переноска для животных' => 'Переноска для животных.jpg',
        'Когтеточка' => 'Когтеточка.jpg',
        
        // Ветаптека
        'Средство от блох для кошек' => 'Средство от блох для кошек.jpg',
        'Глистогонное для собак' => 'Глистогонное для собак.jpg',
        'Капли для глаз' => 'Капли для глаз.webp',
    ];

    if (isset($images[$productName])) return 'images/' . $images[$productName];
    foreach ($images as $key => $img) {
        if (strpos($productName, $key) !== false) return 'images/' . $img;
    }
    if ($categoryId == 1) return 'images/cat.jpg';
    if ($categoryId == 2) return 'images/dog.jpeg';
    if ($categoryId == 3) return 'images/hamster.avif';
    if ($categoryId == 4) return 'images/bird.jpg';
    if ($categoryId == 5) return 'images/fish.jpg';
    return 'images/catdog.png';
}

function getPetImage($petName, $categoryId) {
    $petImages = ['Барсик' => 'Барсик.jpg', 'Мурка' => 'Мурка.jpg', 'Рекс' => 'Рекс.jpg', 'Лайма' => 'Лайма.jpg', 'Хома' => 'Хома.jpg', 'Кеша' => 'Кеша.jpeg'];
    if (isset($petImages[$petName])) return 'images/' . $petImages[$petName];
    if ($categoryId == 1) return 'images/cat.jpg';
    if ($categoryId == 2) return 'images/dog.jpeg';
    if ($categoryId == 3) return 'images/hamster.avif';
    if ($categoryId == 4) return 'images/bird.jpg';
    if ($categoryId == 5) return 'images/fish.jpg';
    return 'images/catdog.png';
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог - Кашатео</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .catalog-page { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .page-title { text-align: center; font-size: 2rem; color: #004d40; margin: 30px 0 20px; padding-bottom: 15px; border-bottom: 3px solid #b2dfdb; display: inline-block; }
        .title-wrapper { text-align: center; margin-bottom: 30px; }
        .category-tabs { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-bottom: 30px; }
        .category-tab { padding: 10px 25px; background: white; border: 1px solid #b2dfdb; border-radius: 30px; text-decoration: none; color: #00695c; transition: all 0.3s; }
        .category-tab:hover, .category-tab.active { background: #00695c; color: white; border-color: #00695c; }
        .catalog-layout { display: flex; gap: 30px; }
        .filters-sidebar { width: 280px; background: white; padding: 20px; border-radius: 16px; position: sticky; top: 20px; }
        .filters-sidebar h3 { color: #004d40; margin-bottom: 20px; }
        .filter-section { margin-bottom: 25px; }
        .filter-section label { display: block; font-weight: 600; margin-bottom: 10px; }
        .filter-section select, .price-inputs input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .price-inputs { display: flex; gap: 10px; }
        .apply-filters-btn { width: 100%; padding: 12px; background: #00695c; color: white; border: none; border-radius: 8px; cursor: pointer; margin-top: 10px; }
        .reset-filters { display: block; text-align: center; margin-top: 15px; color: #999; }
        .products-area { flex: 1; }
        .products-count { background: white; padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; }
        .catalog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
        .card { background: white; border-radius: 16px; overflow: hidden; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        .card-image { height: 180px; background: linear-gradient(135deg, #e0f2f1, #b2dfdb); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .card-image img { width: 100%; height: 100%; object-fit: cover; }
        .card-content { padding: 15px; }
        .card-category { color: #999; font-size: 0.75rem; margin-bottom: 8px; }
        .card-title { font-size: 1rem; font-weight: 600; color: #004d40; min-height: 40px; }
        .card-price { font-size: 1.2rem; font-weight: bold; color: #00695c; margin: 10px 0; }
        .type-switch { display: flex; justify-content: center; gap: 15px; margin-bottom: 30px; }
        .type-btn { padding: 10px 30px; background: white; border: 2px solid #b2dfdb; border-radius: 40px; text-decoration: none; color: #00695c; font-weight: 600; }
        .type-btn.active { background: #00695c; color: white; border-color: #00695c; }
        @media (max-width: 900px) { .catalog-layout { flex-direction: column; } .filters-sidebar { width: 100%; position: static; } }
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
    <main class="catalog-page">
        <div class="title-wrapper"><h1 class="page-title">📋 Каталог</h1></div>
        <div class="type-switch"><a href="catalog.php?type=products" class="type-btn <?= $type == 'products' ? 'active' : '' ?>">📦 Товары</a><a href="catalog.php?type=pets" class="type-btn <?= $type == 'pets' ? 'active' : '' ?>">🐾 Животные</a></div>
        <div class="category-tabs">
            <a href="catalog.php?type=<?= $type ?>" class="category-tab <?= !$category_id ? 'active' : '' ?>">Все</a>
            <a href="catalog.php?type=<?= $type ?>&category=1" class="category-tab <?= $category_id == 1 ? 'active' : '' ?>">🐱 Кошки</a>
            <a href="catalog.php?type=<?= $type ?>&category=2" class="category-tab <?= $category_id == 2 ? 'active' : '' ?>">🐶 Собаки</a>
            <a href="catalog.php?type=<?= $type ?>&category=3" class="category-tab <?= $category_id == 3 ? 'active' : '' ?>">🐹 Грызуны</a>
            <a href="catalog.php?type=<?= $type ?>&category=4" class="category-tab <?= $category_id == 4 ? 'active' : '' ?>">🐦 Птицы</a>
            <a href="catalog.php?type=<?= $type ?>&category=5" class="category-tab <?= $category_id == 5 ? 'active' : '' ?>">🐠 Рыбы</a>
            <a href="catalog.php?type=<?= $type ?>&category=6" class="category-tab <?= $category_id == 6 ? 'active' : '' ?>">🍖 Корма</a>
            <a href="catalog.php?type=<?= $type ?>&category=7" class="category-tab <?= $category_id == 7 ? 'active' : '' ?>">🎒 Аксессуары</a>
            <a href="catalog.php?type=<?= $type ?>&category=8" class="category-tab <?= $category_id == 8 ? 'active' : '' ?>">💊 Ветаптека</a>
        </div>
        <div class="catalog-layout">
            <aside class="filters-sidebar">
                <h3>🔍 Фильтры</h3>
                <form method="GET">
                    <input type="hidden" name="type" value="<?= $type ?>">
                    <?php if ($category_id): ?><input type="hidden" name="category" value="<?= $category_id ?>"><?php endif; ?>
                    <?php if ($type == 'products' && !empty($manufacturers)): ?>
                    <div class="filter-section">
                        <label>🏭 Производители</label>
                        <select name="manufacturer" onchange="this.form.submit()">
                            <option value="">Все производители</option>
                            <?php foreach ($manufacturers as $m): ?>
                                <option value="<?= htmlspecialchars($m['brand']) ?>" <?= $manufacturer == $m['brand'] ? 'selected' : '' ?>><?= htmlspecialchars($m['brand']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="filter-section">
                        <label>💰 Цена</label>
                        <div class="price-inputs">
                            <input type="number" name="min_price" value="<?= $min_price ?>" placeholder="от">
                            <span>—</span>
                            <input type="number" name="max_price" value="<?= $max_price ?>" placeholder="до">
                        </div>
                    </div>
                    <button type="submit" class="apply-filters-btn">Применить фильтр</button>
                    <a href="catalog.php?type=<?= $type ?>" class="reset-filters">Сбросить все фильтры</a>
                </form>
            </aside>
            <div class="products-area">
                <div class="products-count"><?= $type == 'products' ? '📦' : '🐾' ?> Найдено <?= $type == 'products' ? 'товаров' : 'животных' ?>: <?= count($items) ?></div>
                <div class="catalog-grid">
                    <?php if (empty($items)): ?>
                        <div style="text-align: center; padding: 60px;">😕 Ничего не найдено</div>
                    <?php else: foreach ($items as $item): 
                        $img_path = $item['type'] == 'product' ? getProductImage($item['name'], $item['category_id'] ?? 0) : getPetImage($item['name'], $item['category_id'] ?? 0);
                    ?>
                        <div class="card">
                            <div class="card-image"><img src="<?= $img_path ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.parentElement.innerHTML='<div style=\'font-size:3rem\'>' + (<?= $item['type'] == 'pet' ? '\'🐕\'' : '\'📦\'' ?> ) + '</div>'"></div>
                            <div class="card-content">
                                <div class="card-category"><?= htmlspecialchars($item['category_name'] ?? 'Товар') ?></div>
                                <div class="card-title"><?= htmlspecialchars(mb_substr($item['name'], 0, 40)) ?></div>
                                <?php if (isset($item['brand']) && $item['brand']): ?><div class="card-brand">🏷️ <?= htmlspecialchars($item['brand']) ?></div><?php endif; ?>
                                <div class="card-price"><?= number_format($item['price'], 0, '', ' ') ?> ₽</div>
                                <button class="btn" onclick="addToCart('<?= $item['type'] ?>', <?= $item['id'] ?>)">🛒 В корзину</button>
                                <a href="<?= $item['type'] == 'pet' ? 'product.php?type=pet&id=' : 'product.php?type=product&id=' ?><?= $item['id'] ?>" class="btn-outline" style="display: block; text-align: center; margin-top: 8px;">Подробнее</a>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </main>
    <footer class="footer"><div class="container"><p>🐾 Кашатео - Лучший зоомагазин в Ярославле</p><p>&copy; 2024 Все права защищены</p></div></footer>
    <script>function addToCart(type, id){ window.location.href = `cart.php?action=add&type=${type}&id=${id}&quantity=1`; }</script>
</body>
</html>