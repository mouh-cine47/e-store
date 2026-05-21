<?php
include '../includes/header.php';
include '../includes/sidebar.php';

$pdo = Database::connection();

$productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
$hasProducts = (bool)$productsTableStmt->fetch();

$usersTableStmt = $pdo->query("SHOW TABLES LIKE 'users'");
$hasUsers = (bool)$usersTableStmt->fetch();

// Fetch stats
$total_products = 0;
if ($hasProducts) {
    $total_products = (int)($pdo->query('SELECT COUNT(*) AS count FROM products')->fetch()['count'] ?? 0);
}

$ordersTableStmt = $pdo->query("SHOW TABLES LIKE 'orders'");
$hasOrders = (bool)$ordersTableStmt->fetch();

$total_orders = 0;
$total_users = 0;
$recentOrders = [];
$total_revenue = 0;
$total_page_views = 0;
$mostPopularProducts = [];
$salesByCategory = [];

if ($hasOrders) {
    $total_orders = (int)($pdo->query('SELECT COUNT(*) AS count FROM orders')->fetch()['count'] ?? 0);
    if ($hasUsers) {
        $total_users = (int)($pdo->query("SELECT COUNT(*) AS count FROM users WHERE role = 'client'")->fetch()['count'] ?? 0);
    }
    
    // Total revenue from delivered orders
    $revenueStmt = $pdo->query("SELECT SUM(total) AS revenue FROM orders WHERE status = 'delivered'");
    $total_revenue = (float)($revenueStmt->fetch()['revenue'] ?? 0);
    
    $recentOrders = $pdo->query(
        'SELECT o.id, o.total, o.status, o.created_at, u.name '
        . 'FROM orders o '
        . 'JOIN users u ON u.id = o.user_id '
        . 'ORDER BY o.created_at DESC '
        . 'LIMIT 5'
    );
}

// Total page views from product_views table
$viewsTableStmt = $pdo->query("SHOW TABLES LIKE 'product_views'");
$hasProductViews = (bool)$viewsTableStmt->fetch();

if ($hasProductViews) {
    $viewsStmt = $pdo->query('SELECT COUNT(*) AS total_views FROM product_views');
    $total_page_views = (int)($viewsStmt->fetch()['total_views'] ?? 0);
    
    // Most popular products by views
    $mostPopularStmt = $pdo->query(
        'SELECT p.id, p.name, COUNT(v.id) AS view_count '
        . 'FROM product_views v '
        . 'JOIN products p ON p.id = v.product_id '
        . 'GROUP BY v.product_id '
        . 'ORDER BY view_count DESC '
        . 'LIMIT 5'
    );
    $mostPopularProducts = $mostPopularStmt->fetchAll();
}

// Sales by category
if ($hasOrders && $hasProducts) {
    $categoryStmt = $pdo->query(
        'SELECT c.name, COUNT(oi.id) AS items_sold, SUM(oi.price * oi.quantity) AS category_revenue '
        . 'FROM order_items oi '
        . 'JOIN products p ON p.id = oi.product_id '
        . 'JOIN categories c ON c.id = p.category_id '
        . 'JOIN orders o ON o.id = oi.order_id '
        . 'WHERE o.status = "delivered" '
        . 'GROUP BY c.id '
        . 'ORDER BY category_revenue DESC '
        . 'LIMIT 5'
    );
    $salesByCategory = $categoryStmt->fetchAll();
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
    <?php if (!$hasProducts): ?>
        <div class="alert alert-warning">
            Products table is missing. Import the updated database.sql to enable product stats.
        </div>
    <?php endif; ?>
    <?php if (!$hasUsers): ?>
        <div class="alert alert-warning">
            Users table is missing. Import the updated database.sql to enable client stats.
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

    <!-- Additional Stats Row -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stats-card info h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Page Views</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_page_views); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stats-card warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_revenue, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stats-card secondary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Avg Order Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo ($total_orders > 0) ? number_format(($total_revenue / $total_orders), 2) : '0.00'; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Most Popular Products -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 5 Most Viewed Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($mostPopularProducts)): ?>
                                    <?php foreach ($mostPopularProducts as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="badge badge-success"><?php echo (int)$product['view_count']; ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales by Category -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sales by Category</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Items Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($salesByCategory)): ?>
                                    <?php foreach ($salesByCategory as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="badge badge-info"><?php echo (int)$category['items_sold']; ?></span></td>
                                            <td>$<?php echo number_format((float)$category['category_revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No sales data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-12 mb-4">
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
