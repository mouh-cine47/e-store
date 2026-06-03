<?php

class PublicOrderTrackingController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
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

        PageTracker::track($pdo, 'order_tracking', 'Order Tracking');
        
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
        
        include project_path('includes/header.php');
        $this->render('public/order-tracking', get_defined_vars());
    }
}
