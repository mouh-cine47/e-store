<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = Database::connection();

$ordersTableStmt = $pdo->query("SHOW TABLES LIKE 'orders'");
$hasOrders = (bool)$ordersTableStmt->fetch();

$orderItemsTableStmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
$hasOrderItems = (bool)$orderItemsTableStmt->fetch();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

$orders = [];
if ($hasOrders) {
    $orderStmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
    $orderStmt->execute(['user_id' => $_SESSION['user_id']]);
    $orders = $orderStmt->fetchAll();
}

$orderIds = array_map(function ($order) {
    return (int)$order['id'];
}, $orders);

$itemsByOrder = [];
if (count($orderIds) > 0 && $hasOrderItems) {
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
    $itemsStmt = $pdo->prepare(
        'SELECT oi.order_id, oi.quantity, oi.price, p.name '
        . 'FROM order_items oi '
        . 'JOIN products p ON p.id = oi.product_id '
        . 'WHERE oi.order_id IN (' . $placeholders . ')'
    );
    $itemsStmt->execute($orderIds);
    $items = $itemsStmt->fetchAll();

    foreach ($items as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="shop.php">E-Store</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-secondary">Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="cart.php" class="btn btn-outline-primary btn-sm me-2">Cart</a>
                <a href="../auth/logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if (!$hasOrders): ?>
            <div class="alert alert-warning">Orders table is missing. Import database.sql to view orders.</div>
        <?php endif; ?>
        <?php if ($hasOrders && !$hasOrderItems): ?>
            <div class="alert alert-warning">Order items table is missing. Import database.sql to view order details.</div>
        <?php endif; ?>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">My Orders</h4>
            <a href="shop.php" class="btn btn-outline-secondary btn-sm">Continue Shopping</a>
        </div>

        <?php if (count($orders) === 0): ?>
            <div class="alert alert-info">You have not placed any orders yet.</div>
        <?php else: ?>
            <div class="accordion" id="ordersAccordion">
                <?php foreach ($orders as $index => $order): ?>
                    <div class="accordion-item mb-2">
                        <h2 class="accordion-header" id="heading-<?php echo (int)$order['id']; ?>">
                            <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo (int)$order['id']; ?>" aria-expanded="true" aria-controls="collapse-<?php echo (int)$order['id']; ?>">
                                Order #<?php echo (int)$order['id']; ?> - <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?> - $<?php echo number_format((float)$order['total'], 2); ?>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo (int)$order['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#ordersAccordion">
                            <div class="accordion-body">
                                <p class="text-muted mb-2">Placed on <?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <ul class="list-group">
                                    <?php foreach ($itemsByOrder[$order['id']] ?? [] as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?> (x<?php echo (int)$item['quantity']; ?>)</span>
                                            <span>$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
