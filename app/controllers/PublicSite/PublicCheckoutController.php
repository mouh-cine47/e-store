<?php

class PublicCheckoutController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        require_once project_path('app/core/Email.php');
        require_once project_path('includes/csrf.php');
        
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
        $this->render('public/checkout', get_defined_vars());
    }
}
