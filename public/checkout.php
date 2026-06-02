<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/core/Email.php';

$pdo = Database::connection();

$ordersTableStmt = $pdo->query("SHOW TABLES LIKE 'orders'");
$hasOrders = (bool)$ordersTableStmt->fetch();

$orderItemsTableStmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
$hasOrderItems = (bool)$orderItemsTableStmt->fetch();

$statusHistoryTableStmt = $pdo->query("SHOW TABLES LIKE 'order_status_history'");
$hasStatusHistory = (bool)$statusHistoryTableStmt->fetch();

$productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
$hasProducts = (bool)$productsTableStmt->fetch();

$canCheckout = $hasOrders && $hasOrderItems && $hasProducts;

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

$cart = $_SESSION['cart'] ?? [];
if (count($cart) === 0) {
    header('Location: cart.php');
    exit();
}


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        $error = 'Invalid form token. Please refresh and try again.';
    } elseif (!$canCheckout) {
        $error = 'Required tables are missing. Import database.sql to place orders.';
    } else {
        $shippingName = trim($_POST['shipping_name'] ?? '');
        $shippingPhone = trim($_POST['shipping_phone'] ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $country = trim($_POST['country'] ?? '');

        if ($shippingName === '' || $shippingPhone === '' || $shippingAddress === '' || $city === '' || $country === '') {
            $error = 'Please fill in all shipping fields.';
        } else {
            try {
                $pdo->beginTransaction();

                $productIds = array_keys($cart);
                $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                $stmt = $pdo->prepare(
                    'SELECT id, name, price, stock, is_active FROM products WHERE id IN (' . $placeholders . ') FOR UPDATE'
                );
                $stmt->execute($productIds);
                $products = $stmt->fetchAll();

                $productMap = [];
                foreach ($products as $product) {
                    $productMap[$product['id']] = $product;
                }

                $total = 0.0;
                foreach ($cart as $item) {
                    if (!isset($productMap[$item['id']])) {
                        throw new Exception('Some products are no longer available.');
                    }
                    $current = $productMap[$item['id']];
                    if ((int)$current['is_active'] !== 1) {
                        throw new Exception('Some products are no longer available.');
                    }
                    if ((int)$current['stock'] < (int)$item['qty']) {
                        throw new Exception('Not enough stock for ' . $current['name'] . '.');
                    }
                    $total += ((float)$current['price']) * (int)$item['qty'];
                }

                $orderStmt = $pdo->prepare(
                    'INSERT INTO orders (user_id, total, status, shipping_name, shipping_phone, shipping_address, city, country) '
                    . 'VALUES (:user_id, :total, :status, :shipping_name, :shipping_phone, :shipping_address, :city, :country)'
                );
                $orderStmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'total' => $total,
                    'status' => 'pending',
                    'shipping_name' => $shippingName,
                    'shipping_phone' => $shippingPhone,
                    'shipping_address' => $shippingAddress,
                    'city' => $city,
                    'country' => $country,
                ]);
                $orderId = (int)$pdo->lastInsertId();

                $itemStmt = $pdo->prepare(
                    'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)'
                );
                $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - :qty WHERE id = :id');

            foreach ($cart as $item) {
                $current = $productMap[$item['id']];
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'quantity' => (int)$item['qty'],
                    'price' => (float)$current['price'],
                ]);
                $stockStmt->execute([
                    'qty' => (int)$item['qty'],
                    'id' => $item['id'],
                ]);
            }

            // Add initial status history
            if ($hasStatusHistory) {
                $historyStmt = $pdo->prepare(
                    'INSERT INTO order_status_history (order_id, status, message) VALUES (:order_id, :status, :message)'
                );
                $historyStmt->execute([
                    'order_id' => $orderId,
                    'status' => 'pending',
                    'message' => 'Order received and is being processed.'
                ]);
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            
            // Send confirmation email
            Email::sendOrderConfirmation(
                $_SESSION['user_email'],
                $_SESSION['user_name'],
                $orderId,
                number_format($total, 2)
            );
            
            $success = 'Order placed successfully. Check your email for confirmation.';
        } catch (Exception $exception) {
            $pdo->rollBack();
            $error = $exception->getMessage();
        }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="shop.php">E-Store</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-secondary">Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="cart.php" class="btn btn-outline-primary btn-sm me-2">Cart</a>
                <form method="POST" action="../auth/logout.php" class="d-inline">
                    <?php csrf_field(); ?>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="mb-3">Checkout</h4>

        <?php if (!$canCheckout): ?>
            <div class="alert alert-warning">Required tables are missing. Import database.sql to place orders.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <div class="mt-2">
                    <a href="orders.php" class="btn btn-sm btn-success">View Orders</a>
                    <a href="shop.php" class="btn btn-sm btn-outline-secondary">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="POST">
                            <?php csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="shipping_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="shipping_phone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="shipping_address" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" <?php echo $canCheckout ? '' : 'disabled'; ?>>Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cart as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        <small class="text-muted">x<?php echo (int)$item['qty']; ?></small>
                                    </div>
                                    <span>$<?php echo number_format((float)$item['price'] * (int)$item['qty'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
