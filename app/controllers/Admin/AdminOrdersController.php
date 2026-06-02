<?php

class AdminOrdersController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
        require_once project_path('app/core/Email.php');
        
        $pdo = Database::connection();
        
        $success = '';
        $error = '';
        
        $tableStmt = $pdo->query("SHOW TABLES LIKE 'orders'");
        $hasOrders = (bool)$tableStmt->fetch();
        
        $itemsTableStmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
        $hasOrderItems = (bool)$itemsTableStmt->fetch();
        
        $historyTableStmt = $pdo->query("SHOW TABLES LIKE 'order_status_history'");
        $hasStatusHistory = (bool)$historyTableStmt->fetch();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasOrders) {
            if (!csrf_validate()) {
                $error = 'Invalid form token. Please refresh and try again.';
            } else {
                $action = $_POST['action'] ?? '';
                $orderId = (int)($_POST['order_id'] ?? 0);
                $status = $_POST['status'] ?? '';
                $allowedStatuses = ['pending', 'shipped', 'delivered'];
        
                if ($action === 'update_status' && $orderId > 0) {
                    if (!in_array($status, $allowedStatuses, true)) {
                        $error = 'Invalid order status.';
                    } else {
                        // Get current order details
                        $currentStmt = $pdo->prepare('SELECT o.status, o.user_id, u.email, u.name FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = :id');
                    $currentStmt->execute(['id' => $orderId]);
                    $currentOrder = $currentStmt->fetch();
        
                    if ($currentOrder && $currentOrder['status'] !== $status) {
                        // Update order status
                        $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
                        $stmt->execute([
                            'status' => $status,
                            'id' => $orderId,
                        ]);
        
                        // Log status change to history
                        if ($hasStatusHistory) {
                            $statusMessages = [
                                'pending' => 'Order received and is being processed.',
                                'shipped' => 'Your order is on its way!',
                                'delivered' => 'Order has been successfully delivered.'
                            ];
                            $historyStmt = $pdo->prepare(
                                'INSERT INTO order_status_history (order_id, status, message) VALUES (:order_id, :status, :message)'
                            );
                            $historyStmt->execute([
                                'order_id' => $orderId,
                                'status' => $status,
                                'message' => $statusMessages[$status] ?? ''
                            ]);
                        }
        
                        // Send email notification
                        Email::sendOrderStatusNotification(
                            $currentOrder['email'],
                            $currentOrder['name'],
                            $orderId,
                            $currentOrder['status'],
                            $status
                        );
        
                        $success = 'Order status updated and customer notified via email.';
                    } else {
                        $success = 'Order status is already ' . $status . '.';
                    }
                }
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
        $this->render('admin/orders', get_defined_vars());
    }
}
