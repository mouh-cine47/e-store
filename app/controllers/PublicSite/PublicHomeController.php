<?php

class PublicHomeController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        require_once project_path('includes/csrf.php');
        $pdo = Database::connection();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../auth/login.php');
            exit();
        }
        
        // Redirect admin to dashboard
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
            exit();
        }
        
        // Get featured products
        $featuredProducts = [];
        $stmt = $pdo->query("
            SELECT id, name, price, image, stock, description, category_id
            FROM products 
            WHERE is_active = 1 
            ORDER BY views DESC, created_at DESC 
            LIMIT 8
        ");
        $featuredProducts = $stmt->fetchAll();
        
        // Get categories for quick access
        $categories = [];
        $stmt = $pdo->query("SELECT id, name FROM categories LIMIT 6");
        $categories = $stmt->fetchAll();
        
        function excerpt($text, $limit = 100) {
            $text = trim((string)$text);
            if ($text === '') {
                return 'Premium quality product.';
            }
            $text = preg_replace('/\s+/', ' ', $text);
            if (strlen($text) <= $limit) {
                return $text;
            }
            return substr($text, 0, $limit - 3) . '...';
        }
        $this->render('public/home', get_defined_vars());
    }
}
