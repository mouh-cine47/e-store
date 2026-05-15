<?php
include '../includes/header.php';
include '../includes/sidebar.php';
$pdo = Database::connection();

// Fetch stats
$total_products = (int)($pdo->query('SELECT COUNT(*) AS count FROM products')->fetch()['count'] ?? 0);

$ordersTableStmt = $pdo->query("SHOW TABLES LIKE 'orders'");
$hasOrders = (bool)$ordersTableStmt->fetch();

$total_orders = 0;
$total_users = 0;
$recentOrders = [];

if ($hasOrders) {
    $total_orders = (int)($pdo->query('SELECT COUNT(*) AS count FROM orders')->fetch()['count'] ?? 0);
    $total_users = (int)($pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'client'")->fetch()['count'] ?? 0);
    $recentOrders = $pdo->query(
        'SELECT o.id, o.total, o.status, o.created_at, u.name '
        . 'FROM orders o '
        . 'JOIN users u ON u.id = o.user_id '
        . 'ORDER BY o.created_at DESC '
        . 'LIMIT 5'
    );
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <?php if (!$hasOrders): ?>
        <div class="alert alert-warning">
            Orders table is missing. Import the updated database.sql to enable order stats.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stats-card primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stats-card success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stats-card danger h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Clients</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Client</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($hasOrders): ?>
                                    <?php while ($row = $recentOrders->fetch()): ?>
                                <tr>
                                    <td>#<?php echo (int)$row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>$<?php echo number_format((float)$row['total'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if (!$hasOrders): ?>
                            <div class="alert alert-info">No orders yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
</div>
<?php include '../includes/footer.php'; ?>
