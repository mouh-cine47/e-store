<?php
include '../includes/header.php';
include '../includes/sidebar.php';
$pdo = Database::connection();

$success = '';
$error = '';

$tableStmt = $pdo->query("SHOW TABLES LIKE 'orders'");
$hasOrders = (bool)$tableStmt->fetch();

$itemsTableStmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
$hasOrderItems = (bool)$itemsTableStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasOrders) {
    $action = $_POST['action'] ?? '';
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $allowedStatuses = ['pending', 'shipped', 'delivered'];

    if ($action === 'update_status' && $orderId > 0) {
        if (!in_array($status, $allowedStatuses, true)) {
            $error = 'Invalid order status.';
        } else {
            $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
            $stmt->execute([
                'status' => $status,
                'id' => $orderId,
            ]);
            $success = 'Order status updated.';
        }
    }
}

$orders = [];
$itemsByOrder = [];

if ($hasOrders) {
    $ordersStmt = $pdo->query(
        'SELECT o.id, o.total, o.status, o.created_at, u.name, u.email '
        . 'FROM orders o '
        . 'JOIN users u ON u.id = o.user_id '
        . 'ORDER BY o.created_at DESC'
    );
    $orders = $ordersStmt->fetchAll();

    $orderIds = array_map(function ($order) {
        return (int)$order['id'];
    }, $orders);

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
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Orders</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if (!$hasOrders): ?>
        <div class="alert alert-warning">Orders table is missing. Import database.sql to enable order management.</div>
    <?php endif; ?>
    <?php if ($hasOrders && !$hasOrderItems): ?>
        <div class="alert alert-warning">Order items table is missing. Import database.sql to view order items.</div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Orders</h6>
        </div>
        <div class="card-body">
            <?php if (!$hasOrders): ?>
                <div class="alert alert-info mb-0">Orders table is missing.</div>
            <?php elseif (count($orders) === 0): ?>
                <div class="alert alert-info mb-0">No orders yet.</div>
            <?php else: ?>
                <div class="accordion" id="ordersAdminAccordion">
                    <?php foreach ($orders as $index => $order): ?>
                        <div class="accordion-item mb-2">
                            <h2 class="accordion-header" id="heading-<?php echo (int)$order['id']; ?>">
                                <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo (int)$order['id']; ?>" aria-expanded="true" aria-controls="collapse-<?php echo (int)$order['id']; ?>">
                                    Order #<?php echo (int)$order['id']; ?> - <?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?> - $<?php echo number_format((float)$order['total'], 2); ?>
                                </button>
                            </h2>
                            <div id="collapse-<?php echo (int)$order['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#ordersAdminAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <p class="mb-1"><strong>Client:</strong> <?php echo htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="mb-3"><strong>Placed:</strong> <?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <div class="col-lg-6">
                                            <form method="POST" class="d-flex align-items-end gap-2">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?php echo (int)$order['id']; ?>">
                                                <div>
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                    <hr>
                                    <h6>Items</h6>
                                    <?php if (!$hasOrderItems): ?>
                                        <div class="alert alert-info mb-0">Order items table is missing.</div>
                                    <?php else: ?>
                                        <ul class="list-group">
                                            <?php foreach ($itemsByOrder[$order['id']] ?? [] as $item): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?> (x<?php echo (int)$item['quantity']; ?>)</span>
                                                    <span>$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
