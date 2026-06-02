<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = Database::connection();

$ordersTableStmt = $pdo->query("SHOW TABLES LIKE 'orders'");
$hasOrders = (bool)$ordersTableStmt->fetch();

$statusHistoryTableStmt = $pdo->query("SHOW TABLES LIKE 'order_status_history'");
$hasStatusHistory = (bool)$statusHistoryTableStmt->fetch();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get order ID from URL or session
$orderId = null;
$order = null;
$statusHistory = [];
$orderItems = [];

if (isset($_GET['order_id'])) {
    $orderId = (int)$_GET['order_id'];
}

if ($orderId && $hasOrders) {
    // Verify order belongs to current user
    $orderStmt = $pdo->prepare(
        'SELECT o.id, o.user_id, o.total, o.status, o.shipping_name, o.shipping_address, o.city, o.country, o.created_at '
        . 'FROM orders o '
        . 'WHERE o.id = :id AND o.user_id = :user_id'
    );
    $orderStmt->execute([
        'id' => $orderId,
        'user_id' => $_SESSION['user_id']
    ]);
    $order = $orderStmt->fetch();

    if ($order && $hasStatusHistory) {
        // Get status history
        $historyStmt = $pdo->prepare(
            'SELECT status, message, created_at FROM order_status_history WHERE order_id = :order_id ORDER BY created_at ASC'
        );
        $historyStmt->execute(['order_id' => $orderId]);
        $statusHistory = $historyStmt->fetchAll();
    }

    // Get order items
    if ($order) {
        $itemsStmt = $pdo->prepare(
            'SELECT oi.quantity, oi.price, p.name, p.image '
            . 'FROM order_items oi '
            . 'JOIN products p ON p.id = oi.product_id '
            . 'WHERE oi.order_id = :order_id'
        );
        $itemsStmt->execute(['order_id' => $orderId]);
        $orderItems = $itemsStmt->fetchAll();
    }
}

// Get all orders for current user
$allOrders = [];
if ($hasOrders) {
    $userOrdersStmt = $pdo->prepare(
        'SELECT id, total, status, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC'
    );
    $userOrdersStmt->execute(['user_id' => $_SESSION['user_id']]);
    $allOrders = $userOrdersStmt->fetchAll();
}

include '../includes/header.php';
?>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 25px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(to bottom, #007bff, #28a745, #17a2b8);
}

.timeline-item {
    position: relative;
    padding-left: 80px;
    margin-bottom: 30px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 5px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    border: 4px solid #007bff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.timeline-item.active::before {
    background: #007bff;
    box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.15);
}

.timeline-item.completed::before {
    background: #28a745;
    border-color: #28a745;
}

.timeline-date {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.timeline-title {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.timeline-message {
    color: #666;
    font-size: 14px;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 12px;
    text-transform: uppercase;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.shipped {
    background: #cfe2ff;
    color: #084298;
}

.status-badge.delivered {
    background: #d1e7dd;
    color: #0f5132;
}

.order-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

.order-card.active {
    border-left: 4px solid #007bff;
}
</style>

<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-lg-8">
            <?php if (!$hasOrders): ?>
                <div class="alert alert-warning" role="alert">
                    <strong>⚠️ No orders available</strong>
                    <p>The orders system is not yet set up. Please contact support.</p>
                </div>
            <?php elseif (!$order && $orderId): ?>
                <div class="alert alert-danger" role="alert">
                    <strong>❌ Order Not Found</strong>
                    <p>The order you're looking for doesn't exist or you don't have access to it.</p>
                </div>
            <?php elseif ($order): ?>
                <div class="card shadow">
                    <div class="card-header bg-light py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Order #<?php echo (int)$order['id']; ?></h5>
                                <small class="text-muted"><?php echo date('F d, Y - H:i', strtotime($order['created_at'])); ?></small>
                            </div>
                            <span class="status-badge <?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo ucfirst(htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8')); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Timeline -->
                        <h6 class="mb-4"><i class="fas fa-clock"></i> Order Status Timeline</h6>
                        <div class="timeline">
                            <?php
                            $statusSteps = ['pending' => 'Order Placed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
                            $statusIcons = ['pending' => '📋', 'shipped' => '📦', 'delivered' => '✅'];
                            $currentStatusIndex = array_search($order['status'], array_keys($statusSteps));
                            $statusKeys = array_keys($statusSteps);
                            
                            foreach ($statusHistory as $index => $history):
                                $historyStatus = $history['status'];
                                $statusIndex = array_search($historyStatus, $statusKeys);
                                $isActive = ($statusIndex === $currentStatusIndex);
                                $isCompleted = ($statusIndex < $currentStatusIndex);
                            ?>
                                <div class="timeline-item <?php echo $isCompleted ? 'completed' : ($isActive ? 'active' : ''); ?>">
                                    <div class="timeline-date"><?php echo date('F d, Y H:i:s', strtotime($history['created_at'])); ?></div>
                                    <div class="timeline-title"><?php echo $statusIcons[$historyStatus] ?? '•'; ?> <?php echo ucfirst(htmlspecialchars($historyStatus, ENT_QUOTES, 'UTF-8')); ?></div>
                                    <?php if (!empty($history['message'])): ?>
                                        <div class="timeline-message"><?php echo htmlspecialchars($history['message'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="my-4">

                        <!-- Order Details -->
                        <h6 class="mb-3"><i class="fas fa-box"></i> Order Items</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Product" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px; border-radius: 3px;">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td class="text-end"><?php echo (int)$item['quantity']; ?></td>
                                            <td class="text-end">$<?php echo number_format((float)$item['price'], 2); ?></td>
                                            <td class="text-end">$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong>$<?php echo number_format((float)$order['total'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <hr class="my-4">

                        <!-- Shipping Details -->
                        <h6 class="mb-3"><i class="fas fa-truck"></i> Shipping Address</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Name:</strong></p>
                                <p class="text-muted"><?php echo htmlspecialchars($order['shipping_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>City:</strong></p>
                                <p class="text-muted"><?php echo htmlspecialchars($order['city'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Address:</strong></p>
                                <p class="text-muted"><?php echo htmlspecialchars($order['shipping_address'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Country:</strong></p>
                                <p class="text-muted"><?php echo htmlspecialchars($order['country'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    <strong>ℹ️ Select an order</strong>
                    <p>Choose an order from the list on the right to view its tracking details.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Orders Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="m-0"><i class="fas fa-list"></i> My Orders</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (count($allOrders) === 0): ?>
                        <div class="alert alert-info m-3 mb-0">
                            You haven't placed any orders yet.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($allOrders as $o): ?>
                                <a href="?order_id=<?php echo (int)$o['id']; ?>" class="list-group-item list-group-item-action order-card <?php echo ($order && $order['id'] === (int)$o['id']) ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Order #<?php echo (int)$o['id']; ?></h6>
                                            <small class="text-muted"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></small>
                                        </div>
                                        <span class="status-badge <?php echo htmlspecialchars($o['status'], ENT_QUOTES, 'UTF-8'); ?>" style="font-size: 10px;">
                                            <?php echo substr(ucfirst($o['status']), 0, 3); ?>
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-primary">$<?php echo number_format((float)$o['total'], 2); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
