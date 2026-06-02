<?php

class ProductDeleteController extends Controller
{
    public function index()
    {
        require_once project_path('includes/auth_check.php');
        // Bootstrap loaded by the route wrapper.
        require_once project_path('includes/csrf.php');
        
        $pdo = Database::connection();
        
        $productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
        $hasProducts = (bool)$productsTableStmt->fetch();
        
        if (!$hasProducts) {
            header('Location: index.php?error=missing_table');
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
            header('Location: index.php?error=invalid_request');
            exit();
        }
        
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
        
            if ($stmt->execute(['id' => $id])) {
                header('Location: index.php?msg=deleted');
            } else {
                header('Location: index.php?error=failed');
            }
        }
        exit();
    }
}
