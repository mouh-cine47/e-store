<?php

class PublicProductController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        require_once project_path('includes/csrf.php');
        $pdo = Database::connection();
        
        $productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
        $hasProducts = (bool)$productsTableStmt->fetch();
        
        $productViewsStmt = $pdo->query("SHOW TABLES LIKE 'product_views'");
        $hasProductViews = (bool)$productViewsStmt->fetch();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../auth/login.php');
            exit();
        }
        
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
            exit();
        }
        
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $product = null;
        if ($hasProducts) {
            $stmt = $pdo->prepare(
                'SELECT p.id, p.name, p.description, p.price, p.stock, p.image, p.brand, p.color, p.size, p.collection_name, c.name AS category_name '
                . 'FROM products p '
                . 'LEFT JOIN categories c ON c.id = p.category_id '
                . 'WHERE p.id = :id AND p.is_active = 1 '
                . 'LIMIT 1'
            );
            $stmt->execute(['id' => $productId]);
            $product = $stmt->fetch();
        }
        
        
        if ($product && $hasProductViews) {
            if (!isset($_SESSION['viewed_products']) || !is_array($_SESSION['viewed_products'])) {
                $_SESSION['viewed_products'] = [];
            }
        
            if (!in_array($productId, $_SESSION['viewed_products'], true)) {
                $_SESSION['viewed_products'][] = $productId;
                $ip = Geo::getClientIp();
                $geo = Geo::lookup($ip);
        
                $viewStmt = $pdo->prepare(
                    'INSERT INTO product_views (product_id, user_id, ip, city, country) '
                    . 'VALUES (:product_id, :user_id, :ip, :city, :country)'
                );
                $viewStmt->execute([
                    'product_id' => $productId,
                    'user_id' => $_SESSION['user_id'],
                    'ip' => $ip,
                    'city' => $geo['city'],
                    'country' => $geo['country'],
                ]);
        
                $pdo->prepare('UPDATE products SET views = views + 1 WHERE id = :id')->execute(['id' => $productId]);
            }
        }
        
        if (!$product) {
            http_response_code(404);
        }
        $this->render('public/product', get_defined_vars());
    }
}
