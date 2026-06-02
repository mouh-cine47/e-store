<?php

class PublicOrdersController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        require_once project_path('includes/csrf.php');
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
        $this->render('public/orders', get_defined_vars());
    }
}
