<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = Database::connection();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? '';
$redirect = $_SERVER['HTTP_REFERER'] ?? 'cart.php';

if ($action === 'add') {
    $productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $hasProducts = (bool)$productsTableStmt->fetch();
    if (!$hasProducts) {
        header('Location: ' . $redirect);
        exit();
    }
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);
    if ($qty < 1) {
        $qty = 1;
    }

    $stmt = $pdo->prepare(
        'SELECT id, name, price, stock, image FROM products WHERE id = :id AND is_active = 1 LIMIT 1'
    );
    $stmt->execute(['id' => $productId]);
    $product = $stmt->fetch();

    if (!$product || (int)$product['stock'] <= 0) {
        header('Location: ' . $redirect);
        exit();
    }

    $available = (int)$product['stock'];
    if ($qty > $available) {
        $qty = $available;
    }

    if (isset($_SESSION['cart'][$productId])) {
        $currentQty = (int)$_SESSION['cart'][$productId]['qty'];
        $newQty = $currentQty + $qty;
        if ($newQty > $available) {
            $newQty = $available;
        }
        $_SESSION['cart'][$productId]['qty'] = $newQty;
    } else {
        $_SESSION['cart'][$productId] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'qty' => $qty,
            'image' => $product['image'],
            'stock' => $available,
        ];
    }

    header('Location: ' . $redirect);
    exit();
}

if ($action === 'update') {
    $quantities = $_POST['qty'] ?? [];
    foreach ($_SESSION['cart'] as $itemId => $item) {
        if (!isset($quantities[$itemId])) {
            continue;
        }
        $qty = (int)$quantities[$itemId];
        if ($qty < 1) {
            $qty = 1;
        }
        if ($qty > (int)$item['stock']) {
            $qty = (int)$item['stock'];
        }
        $_SESSION['cart'][$itemId]['qty'] = $qty;
    }

    header('Location: cart.php');
    exit();
}

if ($action === 'remove') {
    $productId = (int)($_POST['product_id'] ?? 0);
    unset($_SESSION['cart'][$productId]);
    header('Location: cart.php');
    exit();
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit();
}

header('Location: cart.php');
exit();
