<?php

class PublicShopBackupController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        $pdo = Database::connection();
        
        $productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
        $hasProducts = (bool)$productsTableStmt->fetch();
        
        $categoriesTableStmt = $pdo->query("SHOW TABLES LIKE 'categories'");
        $hasCategories = (bool)$categoriesTableStmt->fetch();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../auth/login.php');
            exit();
        }
        
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
            exit();
        }
        
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
        
        $search = trim($_GET['search'] ?? '');
        $categoryId = trim($_GET['category'] ?? '');
        $brand = trim($_GET['brand'] ?? '');
        $color = trim($_GET['color'] ?? '');
        $size = trim($_GET['size'] ?? '');
        $collection = trim($_GET['collection'] ?? '');
        $section = trim($_GET['section'] ?? '');
        $minPrice = trim($_GET['min_price'] ?? '');
        $maxPrice = trim($_GET['max_price'] ?? '');
        
        $filters = [];
        $params = [];
        
        $filters[] = 'p.is_active = 1';
        
        if ($search !== '') {
            $filters[] = '(p.name LIKE :search_name OR p.description LIKE :search_desc)';
            $params['search_name'] = '%' . $search . '%';
            $params['search_desc'] = '%' . $search . '%';
        }
        
        if ($categoryId !== '') {
            $filters[] = 'p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        
        if ($brand !== '') {
            $filters[] = 'p.brand = :brand';
            $params['brand'] = $brand;
        }
        
        if ($color !== '') {
            $filters[] = 'p.color = :color';
            $params['color'] = $color;
        }
        
        if ($size !== '') {
            $filters[] = 'p.size = :size';
            $params['size'] = $size;
        }
        
        if ($collection !== '') {
            $filters[] = 'p.collection_name = :collection';
            $params['collection'] = $collection;
        }
        
        $sectionLabel = '';
        if ($section !== '') {
            $sectionLabel = strtolower($section);
            if ($sectionLabel === 'women' || $sectionLabel === 'men') {
                $filters[] = 'c.name = :section_category';
                $params['section_category'] = ucfirst($sectionLabel);
            } else {
                $sectionLabel = '';
            }
        }
        
        if ($minPrice !== '' && is_numeric($minPrice)) {
            $filters[] = 'p.price >= :min_price';
            $params['min_price'] = $minPrice;
        }
        
        if ($maxPrice !== '' && is_numeric($maxPrice)) {
            $filters[] = 'p.price <= :max_price';
            $params['max_price'] = $maxPrice;
        }
        
        $whereSql = '';
        if (count($filters) > 0) {
            $whereSql = 'WHERE ' . implode(' AND ', $filters);
        }
        
        $categories = [];
        if ($hasCategories) {
            $categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
            $categories = $categoriesStmt->fetchAll();
        }
        
        $brands = [];
        if ($hasProducts) {
            $brandsStmt = $pdo->query('SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand <> "" ORDER BY brand');
            $brands = $brandsStmt->fetchAll();
        }
        
        $colors = [];
        if ($hasProducts) {
            $colorsStmt = $pdo->query('SELECT DISTINCT color FROM products WHERE color IS NOT NULL AND color <> "" ORDER BY color');
            $colors = $colorsStmt->fetchAll();
        }
        
        $sizes = [];
        if ($hasProducts) {
            $sizesStmt = $pdo->query('SELECT DISTINCT size FROM products WHERE size IS NOT NULL AND size <> "" ORDER BY size');
            $sizes = $sizesStmt->fetchAll();
        }
        
        $collections = [];
        if ($hasProducts) {
            $collectionsStmt = $pdo->query('SELECT DISTINCT collection_name FROM products WHERE collection_name IS NOT NULL AND collection_name <> "" ORDER BY collection_name');
            $collections = $collectionsStmt->fetchAll();
        }
        
        $products = [];
        if ($hasProducts) {
            $sql = 'SELECT p.id, p.name, p.price, p.stock, p.image, p.description, c.name AS category_name '
                . 'FROM products p '
                . 'LEFT JOIN categories c ON c.id = p.category_id '
                . $whereSql . ' '
                . 'ORDER BY p.created_at DESC';
        
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll();
        }
        $this->render('public/shop_backup', get_defined_vars());
    }
}
