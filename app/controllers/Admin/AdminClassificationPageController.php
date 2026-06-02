<?php

class AdminClassificationPageController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
        
        $pdo = Database::connection();
        require_once project_path('app/controllers/ClassificationController.php');
        
        $controller = new ClassificationController($pdo);
        $message = '';
        
        $messageType = '';
        $results = [];
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $message = 'Invalid form token. Please refresh and try again.';
                $messageType = 'danger';
            } else {
                $action = $_POST['action'] ?? '';
        
                if ($action === 'classify_all') {
                    $allProducts = isset($_POST['all_products']) ? true : false;
                    $limit = (int)($_POST['limit'] ?? 100);
                    $results = $controller->classifyProducts($allProducts, $limit);
                    
                    if ($results['success']) {
                        $message = "Classification complete! Processed: {$results['total_classified']}, Auto-assigned: {$results['auto_assigned']}";
                        $messageType = 'success';
                    } else {
                        $message = 'Error: ' . $results['error'];
                        $messageType = 'danger';
                    }
                } elseif ($action === 'train') {
                    $trainResults = $controller->trainClassifier();
                    $message = "Training complete! Processed: {$trainResults['processed']} products";
                    $messageType = 'success';
                } elseif ($action === 'classify_single') {
                    $productId = (int)($_POST['product_id'] ?? 0);
                    $results = $controller->classifyProduct($productId);
                    
                    if ($results['success']) {
                        $message = "Product classified successfully";
                        $messageType = 'success';
                    } else {
                        $message = 'Error: ' . $results['error'];
                        $messageType = 'danger';
                    }
                } elseif ($action === 'apply_classification') {
                    $productId = (int)($_POST['product_id'] ?? 0);
                    $categoryId = (int)($_POST['category_id'] ?? 0);
                    $applyResult = $controller->applyClassification($productId, $categoryId);
                    
                    if ($applyResult['success']) {
                        $message = $applyResult['message'];
                        $messageType = 'success';
                    } else {
                        $message = 'Error: ' . $applyResult['error'];
                        $messageType = 'danger';
                    }
                }
            }
        }
        
        // Get products for single classification dropdown
        $productsStmt = $pdo->query('SELECT id, name FROM products ORDER BY name LIMIT 100');
        $products = $productsStmt->fetchAll();
        
        // Get categories for dropdown
        $categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
        $categories = $categoriesStmt->fetchAll();
        $this->render('admin/classification', get_defined_vars());
    }
}
