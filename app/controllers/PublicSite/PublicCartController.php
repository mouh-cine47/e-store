<?php

class PublicCartController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        require_once project_path('includes/csrf.php');
        $pdo = Database::connection();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../auth/login.php');
            exit();
        }
        
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
            exit();
        }
        
        $cart = $_SESSION['cart'] ?? [];
        $total = 0.0;
        foreach ($cart as $item) {
            $total += ((float)$item['price']) * (int)$item['qty'];
        }
        $this->render('public/cart', get_defined_vars());
    }
}
