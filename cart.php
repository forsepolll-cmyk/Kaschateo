<?php
require_once 'config.php';

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = $_GET['type'] ?? 'product';

if ($action == 'add' && $id > 0) {
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    if (isset($_SESSION['cart'][$type][$id])) {
        $_SESSION['cart'][$type][$id]['quantity'] += $quantity;
    } else {
        if ($type == 'pet') {
            $stmt = $pdo->prepare("SELECT name, price FROM pets WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ?");
        }
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        if ($item) {
            $_SESSION['cart'][$type][$id] = ['id' => $id, 'name' => $item['name'], 'price' => $item['price'], 'quantity' => $quantity, 'type' => $type];
        }
    }
    header('Location: cart.php');
    exit;
}

if ($action == 'remove' && $id > 0) {
    if (isset($_SESSION['cart'][$type][$id])) unset($_SESSION['cart'][$type][$id]);
    header('Location: cart.php');
    exit;
}

if ($action == 'update' && $id > 0) {
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
    if ($quantity > 0 && isset($_SESSION['cart'][$type][$id])) $_SESSION['cart'][$type][$id]['quantity'] = $quantity;
    header('Location: cart.php');
    exit;
}

if ($action == 'clear') { $_SESSION['cart'] = []; header('Location: cart.php'); exit; }

$cart_items = [];
$total = 0;
$item_count = 0;
foreach ($_SESSION['cart'] as $type => $items) {
    foreach ($items as $item) {
        $cart_items[] = $item;
        $total += $item['price'] * $item['quantity'];
        $item_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина - Кашатео</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-page { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .page-title { text-align: center; font-size: 2rem; color: #004d40; margin: 30px 0 20px; padding-bottom: 15px; border-bottom: 3px solid #b2dfdb; display: inline-block; }
        .title-wrapper { text-align: center; margin-bottom: 40px; }
        .cart-container { display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
        .cart-items { background: white; border-radius: 20px; padding: 20px; }
        .cart-header { display: grid; grid-template-columns: 3fr 1fr 1fr 0.5fr; padding: 15px 10px; background: #e0f2f1; border-radius: 12px; font-weight: 600; margin-bottom: 15px; }
        .cart-item { display: grid; grid-template-columns: 3fr 1fr 1fr 0.5fr; align-items: center; padding: 15px 10px; border-bottom: 1px solid #eee; }
        .item-name { font-weight: 600; }
        .item-type { font-size: 0.75rem; color: #888; margin-top: 4px; }
        .item-price, .item-total { color: #00695c; font-weight: 600; }
        .item-quantity { display: flex; align-items: center; gap: 8px; }
        .item-quantity input { width: 60px; padding: 6px; text-align: center; border: 1px solid #ddd; border-radius: 6px; }
        .quantity-btn { width: 28px; height: 28px; background: #e0f2f1; border: none; border-radius: 6px; cursor: pointer; }
        .remove-btn { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #e53935; }
        .cart-summary { background: white; border-radius: 20px; padding: 25px; position: sticky; top: 20px; }
        .cart-summary h3 { text-align: center; color: #004d40; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .summary-total { display: flex; justify-content: space-between; padding: 15px 0; font-size: 1.2rem; font-weight: 700; color: #004d40; border-top: 2px solid #b2dfdb; margin-top: 10px; }
        .checkout-btn { width: 100%; padding: 14px; background: #00695c; color: white; border: none; border-radius: 40px; font-size: 1.1rem; cursor: pointer; margin-top: 20px; }
        .clear-cart { display: block; text-align: center; margin-top: 15px; color: #e53935; text-decoration: none; }
        .empty-cart { text-align: center; padding: 60px; background: white; border-radius: 20px; }
        .empty-cart .emoji { font-size: 4rem; margin-bottom: 20px; }
        .continue-shopping { display: inline-block; margin-top: 20px; padding: 12px 30px; background: #00695c; color: white; text-decoration: none; border-radius: 40px; }
        @media (max-width: 800px) { .cart-container { grid-template-columns: 1fr; } .cart-header { display: none; } .cart-item { grid-template-columns: 1fr; gap: 10px; text-align: center; } .item-quantity { justify-content: center; } }
    </style>
</head>
<body>
    <header class="header"><div class="container"><h1>🐾 Кашатео</h1><p class="slogan">Лучший зоомагазин в Ярославле</p></div></header>
    <nav class="nav">
    <div class="container">
        <a href="index.php">🏠 Главная</a>
        <a href="catalog.php?type=products">📦 Каталог</a>
        <a href="cart.php">🛒 Корзина <?= $item_count > 0 ? "($item_count)" : '' ?></a>
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
    <main class="cart-page">
        <div class="title-wrapper"><h1 class="page-title">Корзина</h1></div>
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart"><div class="emoji"></div><h2>Корзина пуста</h2><a href="catalog.php?type=products" class="continue-shopping">Перейти в каталог →</a></div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-header"><div>Товар</div><div>Цена</div><div>Количество</div><div>Сумма</div></div>
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?><div class="item-type"><?= $item['type'] == 'pet' ? '🐾 Животное' : 'Товар' ?></div></div>
                            <div class="item-price"><?= number_format($item['price'], 0, '', ' ') ?> ₽</div>
                            <div class="item-quantity">
                                <button class="quantity-btn" onclick="updateQuantity('<?= $item['type'] ?>', <?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)">-</button>
                                <input type="number" id="qty_<?= $item['type'] ?>_<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" min="1" onchange="updateQuantity('<?= $item['type'] ?>', <?= $item['id'] ?>, this.value)">
                                <button class="quantity-btn" onclick="updateQuantity('<?= $item['type'] ?>', <?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)">+</button>
                            </div>
                            <div class="item-total"><?= number_format($item['price'] * $item['quantity'], 0, '', ' ') ?> ₽</div>
                            <button class="remove-btn" onclick="removeItem('<?= $item['type'] ?>', <?= $item['id'] ?>)"></button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <h3>Итого</h3>
                    <div class="summary-row"><span>Товаров:</span><span><?= $item_count ?> шт.</span></div>
                    <div class="summary-row"><span>Сумма:</span><span><?= number_format($total, 0, '', ' ') ?> ₽</span></div>
                    <div class="summary-total"><span>К оплате:</span><span><?= number_format($total, 0, '', ' ') ?> ₽</span></div>
                    <button class="checkout-btn" onclick="checkout()">Оформить заказ</button>
                    <a href="?action=clear" class="clear-cart" onclick="return confirm('Очистить корзину?')">Очистить корзину</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
    <footer class="footer"><div class="container"><p>🐾 Кашатео - Лучший зоомагазин в Ярославле</p></div></footer>
    <script>
        function updateQuantity(type, id, quantity) { if (quantity < 1) quantity = 1; window.location.href = `cart.php?action=update&type=${type}&id=${id}&quantity=${quantity}`; }
        function removeItem(type, id) { if (confirm('Удалить товар?')) window.location.href = `cart.php?action=remove&type=${type}&id=${id}`; }
        function checkout() { alert('Заказ оформлен!\n\nСпасибо за покупку!'); window.location.href = 'cart.php?action=clear'; }
    </script>
</body>
</html>