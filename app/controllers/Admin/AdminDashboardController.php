<?php

class AdminDashboardController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
        
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
        $this->render('admin/dashboard', get_defined_vars());
    }
}
