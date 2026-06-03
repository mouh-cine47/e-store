<?php

class PublicShopController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        require_once project_path('includes/csrf.php');
        $pdo = Database::connection();
        
        // Authentication & Authorization
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../auth/login.php');
            exit();
        }
        
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
            exit();
        }
        
        // Table existence checks
        $productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
        $hasProducts = (bool)$productsTableStmt->fetch();
        
        $categoriesTableStmt = $pdo->query("SHOW TABLES LIKE 'categories'");
        $hasCategories = (bool)$categoriesTableStmt->fetch();
        
        // Pagination constants
        $itemsPerPage = 12;
        
        // Helper: Text excerpt
        function excerpt($text, $limit = 120) {
            $text = trim((string)$text);
            if (empty($text)) return 'Premium quality product.';
            $text = preg_replace('/\s+/', ' ', $text);
            if (strlen($text) <= $limit) return $text;
            return substr($text, 0, $limit - 3) . '...';
        }
        
        // Get filter parameters (safe via htmlspecialchars)
        $search = trim($_GET['search'] ?? '');
        $categoryId = trim($_GET['category'] ?? '');
        $brand = trim($_GET['brand'] ?? '');
        $color = trim($_GET['color'] ?? '');
        $size = trim($_GET['size'] ?? '');
        $collection = trim($_GET['collection'] ?? '');
        $minPrice = trim($_GET['min_price'] ?? '');
        $maxPrice = trim($_GET['max_price'] ?? '');
        $sort = trim($_GET['sort'] ?? 'newest');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $section = strtolower(trim($_GET['section'] ?? ''));
        $sectionLabels = [
            'men' => 'Men',
            'women' => 'Women',
        ];
        $isSectionPage = isset($sectionLabels[$section]);
        $sectionTitle = $isSectionPage ? $sectionLabels[$section] . ' Collection' : 'Shop Our Collection';
        $sectionDescription = $isSectionPage
            ? 'Browse products selected for the ' . strtolower($sectionLabels[$section]) . ' section'
            : 'Discover modern fashion designed for every occasion';
        $clearUrl = $isSectionPage ? $section . '.php' : 'shop.php';

        PageTracker::track($pdo, $isSectionPage ? $section : 'shop', $sectionTitle);
        
        // Get categories early so Men/Women pages can resolve to real category filters.
        $categories = [];
        $sectionCategoryId = null;
        if ($hasCategories) {
            $categoriesStmt = $pdo->query('SELECT id, name, slug FROM categories ORDER BY name');
            $categories = $categoriesStmt->fetchAll();
        
            if ($isSectionPage) {
                foreach ($categories as $category) {
                    $categoryName = strtolower((string)($category['name'] ?? ''));
                    $categorySlug = strtolower((string)($category['slug'] ?? ''));
                    if ($categoryName === $section || $categorySlug === $section) {
                        $sectionCategoryId = (int)$category['id'];
                        break;
                    }
                }
            }
        }
        
        // Build WHERE clause
        $filters = [];
        $params = [];
        $filters[] = 'p.is_active = 1';
        
        if ($search !== '') {
            $filters[] = '(p.name LIKE :search_name OR p.description LIKE :search_desc)';
            $params['search_name'] = '%' . $search . '%';
            $params['search_desc'] = '%' . $search . '%';
        }
        
        if ($isSectionPage && $sectionCategoryId !== null) {
            $filters[] = 'p.category_id = :section_category_id';
            $params['section_category_id'] = $sectionCategoryId;
            $categoryId = (string)$sectionCategoryId;
        } elseif ($isSectionPage) {
            $filters[] = '1 = 0';
        } elseif ($categoryId !== '') {
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
        
        if ($minPrice !== '' && is_numeric($minPrice)) {
            $filters[] = 'p.price >= :min_price';
            $params['min_price'] = $minPrice;
        }
        
        if ($maxPrice !== '' && is_numeric($maxPrice)) {
            $filters[] = 'p.price <= :max_price';
            $params['max_price'] = $maxPrice;
        }
        
        $whereSql = count($filters) > 0 ? 'WHERE ' . implode(' AND ', $filters) : '';
        
        // Sort logic
        $orderSql = 'ORDER BY p.created_at DESC';
        if ($sort === 'price-asc') {
            $orderSql = 'ORDER BY p.price ASC';
        } elseif ($sort === 'price-desc') {
            $orderSql = 'ORDER BY p.price DESC';
        } elseif ($sort === 'bestselling') {
            $orderSql = 'ORDER BY p.views DESC, p.created_at DESC';
        } elseif ($sort === 'name') {
            $orderSql = 'ORDER BY p.name ASC';
        }
        
        // Get total count for pagination
        $countSql = 'SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON c.id = p.category_id ' . $whereSql;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalProducts = (int)($countStmt->fetch()['total'] ?? 0);
        $totalPages = ceil($totalProducts / $itemsPerPage);
        $offset = ($page - 1) * $itemsPerPage;
        
        // Fetch products with pagination
        $products = [];
        if ($hasProducts) {
            $sql = "SELECT p.id, p.name, p.price, p.stock, p.image, p.description, p.brand, p.color, p.size, p.collection_name, p.created_at, c.name AS category_name 
                    FROM products p 
                    LEFT JOIN categories c ON c.id = p.category_id 
                    $whereSql 
                    $orderSql 
                    LIMIT :offset, :limit";
            
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
            $stmt->execute();
            $products = $stmt->fetchAll();
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
        
        // Get featured categories for mobile
        $featuredCategories = $categories;
        $this->render('public/shop', get_defined_vars());
    }
}
