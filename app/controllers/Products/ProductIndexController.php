<?php

class ProductIndexController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
        $pdo = Database::connection();
        
        $productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
        $hasProducts = (bool)$productsTableStmt->fetch();
        
        $search = trim($_GET['search'] ?? '');
        $params = [];
        $filters = [];
        $whereSql = '';
        
        $hasCategories = false;
        $hasStock = false;
        $hasLegacyCategory = false;
        $products = [];
        
        function excerpt($text, $limit = 120)
        {
            $text = trim((string)$text);
            if ($text === '') {
                return 'No description provided.';
            }
            $text = preg_replace('/\s+/', ' ', $text);
            if (strlen($text) <= $limit) {
                return $text;
            }
            return substr($text, 0, $limit - 3) . '...';
        }
        
        if ($hasProducts) {
            $tableStmt = $pdo->query("SHOW TABLES LIKE 'categories'");
            $hasCategories = (bool)$tableStmt->fetch();
        
            $columnStmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'");
            $hasStock = (bool)$columnStmt->fetch();
            if (!$hasStock) {
                $legacyCategoryStmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'category'");
                $hasLegacyCategory = (bool)$legacyCategoryStmt->fetch();
            }
        
            if ($search !== '') {
                $searchFields = ['p.name'];
                if ($hasStock) {
                    $searchFields[] = 'p.brand';
                    $searchFields[] = 'p.color';
                    $searchFields[] = 'p.size';
                    $searchFields[] = 'p.collection_name';
                }
                if ($hasStock && $hasCategories) {
                    $searchFields[] = 'c.name';
                } elseif (!$hasStock && $hasLegacyCategory) {
                    $searchFields[] = 'p.category';
                }
        
                $searchParts = [];
                foreach ($searchFields as $index => $field) {
                    $param = 'search_' . $index;
                    $searchParts[] = $field . ' LIKE :' . $param;
                    $params[$param] = '%' . $search . '%';
                }
                $filters[] = '(' . implode(' OR ', $searchParts) . ')';
            }
        
            if (count($filters) > 0) {
                $whereSql = 'WHERE ' . implode(' AND ', $filters);
            }
        
            $stockField = $hasStock ? 'p.stock' : 'p.quantity';
            $brandField = $hasStock ? 'p.brand' : 'NULL';
            $activeField = $hasStock ? 'p.is_active' : '1';
            $imageField = $hasStock ? 'p.image' : 'NULL';
            $descriptionField = $hasStock ? 'p.description' : 'NULL';
        
            $categorySelect = 'NULL AS category_name';
            $joinSql = '';
            if ($hasStock && $hasCategories) {
                $categorySelect = 'c.name AS category_name';
                $joinSql = 'LEFT JOIN categories c ON c.id = p.category_id ';
            } elseif (!$hasStock && $hasLegacyCategory) {
                $categorySelect = 'p.category AS category_name';
            }
        
            if ($hasStock && $hasCategories) {
                $sql = 'SELECT p.id, p.name, p.price, ' . $stockField . ' AS stock, ' . $activeField . ' AS is_active, '
                    . $brandField . ' AS brand, ' . $imageField . ' AS image, ' . $descriptionField . ' AS description, ' . $categorySelect . ' '
                    . 'FROM products p '
                    . $joinSql
                    . $whereSql . ' '
                    . 'ORDER BY p.id DESC';
            } else {
                $sql = 'SELECT p.id, p.name, p.price, ' . $stockField . ' AS stock, ' . $activeField . ' AS is_active, '
                    . $brandField . ' AS brand, ' . $imageField . ' AS image, ' . $descriptionField . ' AS description, ' . $categorySelect . ' '
                    . 'FROM products p '
                    . $whereSql . ' '
                    . 'ORDER BY p.id DESC';
            }
        
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
        }
        $this->render('products/index', get_defined_vars());
    }
}
