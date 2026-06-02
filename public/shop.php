<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
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
const ITEMS_PER_PAGE = 12;

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
$minPrice = trim($_GET['min_price'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$page = max(1, (int)($_GET['page'] ?? 1));

// Build WHERE clause
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
$totalPages = ceil($totalProducts / ITEMS_PER_PAGE);
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Fetch products with pagination
$products = [];
if ($hasProducts) {
    $sql = "SELECT p.id, p.name, p.price, p.stock, p.image, p.description, p.brand, p.color, p.size, p.created_at, c.name AS category_name 
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
    $stmt->bindValue(':limit', ITEMS_PER_PAGE, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();
}

// Get filter options for sidebar
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

// Get featured categories for mobile
$featuredCategories = $categories;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Shop — E-Store | Premium Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           CSS VARIABLES & RESET
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        :root {
            /* Colors */
            --color-bg: #F8F7F4;
            --color-white: #FFFFFF;
            --color-text: #111111;
            --color-text-secondary: #666666;
            --color-text-muted: #999999;
            --color-border: #E8E8E8;
            --color-border-light: #F0EFE9;
            --color-accent: #8B9D84;
            --color-accent-dark: #6B7D64;
            --color-accent-light: #E8EFE6;
            --color-success: #10b981;
            --color-error: #ef4444;
            
            /* Shadows */
            --shadow-xs: 0 1px 3px rgba(17, 17, 17, 0.05);
            --shadow-sm: 0 2px 8px rgba(17, 17, 17, 0.08);
            --shadow-md: 0 4px 16px rgba(17, 17, 17, 0.12);
            --shadow-lg: 0 8px 24px rgba(17, 17, 17, 0.15);
            --shadow-xl: 0 12px 40px rgba(17, 17, 17, 0.2);
            
            /* Spacing */
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-2xl: 48px;
            
            /* Border Radius */
            --radius-sm: 4px;
            --radius-md: 8px;
            --radius-lg: 14px;
            --radius-xl: 20px;
            
            /* Transitions */
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.2s ease-out;
            --transition-slow: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            background: var(--color-bg);
            color: var(--color-text);
            font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 16px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           NAVBAR
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .navbar {
            background: var(--color-white);
            border-bottom: 1px solid var(--color-border-light);
            padding: 1.2rem 0;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: var(--shadow-xs);
            transition: padding 0.3s ease, box-shadow 0.3s ease;
        }

        .navbar.navbar--shrink {
            padding: 0.8rem 0;
            box-shadow: var(--shadow-sm);
        }

        .navbar__container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--spacing-xl);
        }

        .navbar__brand {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--color-text);
            text-decoration: none;
            letter-spacing: -0.5px;
            flex-shrink: 0;
        }

        .navbar__nav {
            display: flex;
            gap: var(--spacing-xl);
            align-items: center;
            flex: 1;
            justify-content: center;
        }

        .navbar__link {
            color: var(--color-text-secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            position: relative;
            transition: color 0.2s ease;
            letter-spacing: 0.3px;
        }

        .navbar__link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 1.5px;
            background: var(--color-accent);
            transition: width 0.3s ease;
        }

        .navbar__link:hover {
            color: var(--color-text);
        }

        .navbar__link:hover::after {
            width: 100%;
        }

        .navbar__actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
            margin-left: auto;
        }

        .navbar__icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-text-secondary);
            font-size: 1.1rem;
            transition: color 0.2s ease;
            position: relative;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .navbar__icon-btn:hover {
            color: var(--color-accent);
        }

        .navbar__cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--color-accent);
            color: white;
            font-size: 0.6rem;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .navbar__user {
            display: flex;
            align-items: center;
            gap: var(--spacing);
            font-size: 0.85rem;
            color: var(--color-text-secondary);
        }

        .navbar__logout {
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s ease;
        }

        .navbar__logout:hover {
            color: var(--color-accent);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           HERO SECTION
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .hero {
            background: linear-gradient(135deg, var(--color-accent-light) 0%, var(--color-bg) 100%);
            padding: 100px var(--spacing-lg);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero__content {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
            animation: fadeInDown 0.8s ease-out;
        }

        .hero__title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 7vw, 3.2rem);
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: var(--spacing-lg);
            line-height: 1.15;
            letter-spacing: -0.5px;
        }

        .hero__subtitle {
            font-size: 1.05rem;
            color: var(--color-text-secondary);
            margin-bottom: var(--spacing-xl);
            line-height: 1.7;
            font-weight: 400;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           CONTAINER
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 var(--spacing-lg);
        }

        .shop {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: var(--spacing-2xl);
            padding: var(--spacing-2xl) 0;
            min-height: 100vh;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           FILTERS SIDEBAR
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .filters {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        .filters__title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--color-text);
            letter-spacing: 0.5px;
        }

        .filter {
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-xl);
            border-bottom: 1px solid var(--color-border-light);
        }

        .filter:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .filter__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            margin-bottom: var(--spacing-sm);
        }

        .filter__label {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--color-text);
            transition: color 0.2s ease;
        }

        .filter__header:hover .filter__label {
            color: var(--color-accent);
        }

        .filter__toggle {
            font-size: 0.7rem;
            color: var(--color-text-muted);
            transition: transform 0.3s ease;
        }

        .filter.collapsed .filter__toggle {
            transform: rotate(-90deg);
        }

        .filter__content {
            max-height: 1000px;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .filter.collapsed .filter__content {
            max-height: 0;
        }

        .filter-option {
            display: flex;
            align-items: center;
            margin-bottom: var(--spacing-sm);
        }

        .filter-option input[type="checkbox"],
        .filter-option input[type="radio"] {
            margin-right: var(--spacing-sm);
            accent-color: var(--color-accent);
            cursor: pointer;
        }

        .filter-option label {
            font-size: 0.9rem;
            color: var(--color-text-secondary);
            cursor: pointer;
            transition: color 0.2s ease;
            flex: 1;
        }

        .filter-option input:checked + label {
            color: var(--color-text);
            font-weight: 600;
        }

        .filter-option label:hover {
            color: var(--color-accent);
        }

        .color-swatches {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-sm);
        }

        .color-swatch {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .color-swatch:hover {
            transform: scale(1.15);
        }

        .color-swatch.active {
            border-color: var(--color-accent);
            box-shadow: 0 0 0 2px var(--color-bg), 0 0 0 4px var(--color-accent);
        }

        .size-pills {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-sm);
        }

        .size-pill {
            padding: var(--spacing-sm) var(--spacing);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            background: var(--color-white);
            color: var(--color-text-secondary);
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .size-pill:hover {
            border-color: var(--color-accent);
            color: var(--color-accent);
        }

        .size-pill.active {
            background: var(--color-accent);
            color: white;
            border-color: var(--color-accent);
        }

        .filter-price-inputs {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-sm);
        }

        .filter-price-inputs input {
            flex: 1;
            padding: var(--spacing-sm) var(--spacing);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            font-family: 'DM Sans', sans-serif;
        }

        .filter-price-inputs input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 3px var(--color-accent-light);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           MAIN CONTENT
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .shop__content {
            display: flex;
            flex-direction: column;
        }

        .shop__toolbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: var(--spacing-xl);
            padding-bottom: var(--spacing-lg);
            border-bottom: 1px solid var(--color-border-light);
        }

        .shop__meta {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
        }

        .shop__title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-text);
            letter-spacing: -0.5px;
        }

        .shop__count {
            font-size: 0.9rem;
            color: var(--color-text-secondary);
            font-weight: 500;
        }

        .shop__sort {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .shop__sort-label {
            font-size: 0.9rem;
            color: var(--color-text-secondary);
            font-weight: 500;
        }

        .shop__sort-select {
            padding: var(--spacing-sm) var(--spacing);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            background: var(--color-white);
            color: var(--color-text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .shop__sort-select:hover,
        .shop__sort-select:focus {
            border-color: var(--color-accent);
            outline: none;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           ACTIVE FILTERS
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }

        .filter-tag {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            background: var(--color-accent-light);
            color: var(--color-accent-dark);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-xl);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-tag button {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
            transition: opacity 0.2s ease;
        }

        .filter-tag button:hover {
            opacity: 0.6;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           PRODUCT GRID
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .products {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--spacing-xl);
            margin-bottom: var(--spacing-2xl);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           PRODUCT CARD
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .product-card {
            background: var(--color-white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            animation: cardFadeIn 0.6s ease-out backwards;
        }

        .product-card:nth-child(1) { animation-delay: 0.08s; }
        .product-card:nth-child(2) { animation-delay: 0.16s; }
        .product-card:nth-child(3) { animation-delay: 0.24s; }
        .product-card:nth-child(n+4) { animation-delay: calc((var(--idx, 1) - 1) * 60ms); }

        .product-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .product-image {
            position: relative;
            width: 100%;
            aspect-ratio: 3 / 4;
            background: var(--color-bg);
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-bg);
            color: var(--color-text-muted);
            font-size: 3rem;
        }

        .product-badge {
            position: absolute;
            top: var(--spacing-lg);
            left: var(--spacing-lg);
            background: var(--color-accent);
            color: white;
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            z-index: 2;
        }

        .product-wishlist {
            position: absolute;
            top: var(--spacing-lg);
            right: var(--spacing-lg);
            background: var(--color-white);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--color-text-secondary);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
            z-index: 3;
        }

        .product-wishlist:hover {
            background: var(--color-accent);
            color: white;
            transform: scale(1.1);
        }

        .product-wishlist.active {
            color: #e74c3c;
        }

        .product-quick-add {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: var(--spacing) var(--spacing-lg);
            background: rgba(139, 157, 132, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: white;
            border: none;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(100%);
            z-index: 4;
        }

        .product-card:hover .product-quick-add {
            opacity: 1;
            transform: translateY(0);
        }

        .product-quick-add:hover {
            background: rgba(107, 125, 100, 0.95);
        }

        .product-info {
            padding: var(--spacing-lg);
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            flex: 1;
        }

        .product-brand {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--color-text-muted);
        }

        .product-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--color-text);
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-colors {
            display: flex;
            gap: var(--spacing-xs);
            margin-top: var(--spacing-xs);
        }

        .product-color-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 1px solid var(--color-border);
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--color-text);
            margin-top: auto;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           EMPTY STATE
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .empty-state {
            text-align: center;
            padding: var(--spacing-2xl) var(--spacing-lg);
            grid-column: 1 / -1;
        }

        .empty-state__icon {
            font-size: 4rem;
            color: var(--color-border);
            margin-bottom: var(--spacing-lg);
            opacity: 0.4;
        }

        .empty-state__title {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--color-text);
            margin-bottom: var(--spacing);
            font-weight: 600;
        }

        .empty-state__description {
            color: var(--color-text-secondary);
            margin-bottom: var(--spacing-xl);
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-state__cta {
            display: inline-block;
            padding: var(--spacing) var(--spacing-xl);
            background: var(--color-accent);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.2s ease;
        }

        .empty-state__cta:hover {
            background: var(--color-accent-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           PAGINATION
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-xl) 0;
            border-top: 1px solid var(--color-border-light);
        }

        .pagination__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 var(--spacing-sm);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            color: var(--color-text);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .pagination__link:hover {
            border-color: var(--color-accent);
            color: var(--color-accent);
        }

        .pagination__link.active {
            background: var(--color-accent);
            color: white;
            border-color: var(--color-accent);
        }

        .pagination__link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           MOBILE FILTER DRAWER
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .filter-drawer {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--color-white);
            border-top: 1px solid var(--color-border-light);
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideUp 0.3s ease-out;
        }

        .filter-drawer.active {
            display: flex;
            flex-direction: column;
        }

        .filter-drawer__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--spacing-lg);
            border-bottom: 1px solid var(--color-border-light);
            position: sticky;
            top: 0;
            background: var(--color-white);
        }

        .filter-drawer__title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .filter-drawer__close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--color-text-secondary);
            transition: color 0.2s ease;
        }

        .filter-drawer__close:hover {
            color: var(--color-text);
        }

        .filter-drawer__content {
            padding: var(--spacing-lg);
            flex: 1;
            overflow-y: auto;
        }

        .filter-drawer__actions {
            display: flex;
            gap: var(--spacing);
            padding: var(--spacing-lg);
            border-top: 1px solid var(--color-border-light);
            background: var(--color-white);
        }

        .filter-drawer__btn {
            flex: 1;
            padding: var(--spacing) var(--spacing-lg);
            border-radius: var(--radius-sm);
            border: none;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: all 0.2s ease;
            text-transform: uppercase;
        }

        .filter-drawer__apply {
            background: var(--color-accent);
            color: white;
        }

        .filter-drawer__apply:hover {
            background: var(--color-accent-dark);
        }

        .filter-drawer__reset {
            background: var(--color-bg);
            color: var(--color-text);
            border: 1px solid var(--color-border);
        }

        .filter-drawer__reset:hover {
            background: var(--color-border-light);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           MOBILE CATEGORY CHIPS
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .category-chips {
            display: none;
            overflow-x: auto;
            gap: var(--spacing-sm);
            padding: var(--spacing);
            margin-bottom: var(--spacing-lg);
            scroll-behavior: smooth;
        }

        .category-chips::-webkit-scrollbar {
            height: 4px;
        }

        .category-chips::-webkit-scrollbar-track {
            background: transparent;
        }

        .category-chips::-webkit-scrollbar-thumb {
            background: var(--color-border);
            border-radius: 2px;
        }

        .category-chip {
            display: inline-flex;
            align-items: center;
            padding: var(--spacing-sm) var(--spacing-lg);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            background: var(--color-white);
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .category-chip:hover {
            border-color: var(--color-accent);
            color: var(--color-accent);
        }

        .category-chip.active {
            background: var(--color-accent);
            color: white;
            border-color: var(--color-accent);
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           MOBILE BOTTOM NAVIGATION
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--color-white);
            border-top: 1px solid var(--color-border-light);
            padding: var(--spacing-sm) 0;
            z-index: 900;
        }

        .bottom-nav__content {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .bottom-nav__item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm);
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .bottom-nav__item:hover,
        .bottom-nav__item.active {
            color: var(--color-accent);
        }

        .bottom-nav__icon {
            font-size: 1.4rem;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           FLOATING CART BUTTON
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        .floating-cart {
            display: none;
            position: fixed;
            bottom: 80px;
            right: var(--spacing-lg);
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--color-accent);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.3rem;
            box-shadow: var(--shadow-lg);
            z-index: 800;
            transition: all 0.2s ease;
        }

        .floating-cart:hover {
            transform: scale(1.1);
        }

        .floating-cart__badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--color-error);
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           ANIMATIONS
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cardFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           RESPONSIVE - TABLET (1024px)
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        @media (max-width: 1024px) {
            .shop {
                grid-template-columns: 220px 1fr;
                gap: var(--spacing-lg);
            }

            .products {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing-lg);
            }

            .navbar__nav {
                display: none;
            }

            .hero__title {
                font-size: 2.4rem;
            }
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           RESPONSIVE - MOBILE (768px)
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        @media (max-width: 768px) {
            .shop {
                grid-template-columns: 1fr;
                gap: 0;
                padding: 0;
                padding-bottom: var(--spacing-2xl);
            }

            .filters {
                display: none;
            }

            .category-chips {
                display: flex;
            }

            .bottom-nav {
                display: block;
            }

            .floating-cart {
                display: block;
            }

            body {
                padding-bottom: 80px;
            }

            .navbar__container {
                padding: 0 var(--spacing);
                gap: var(--spacing);
            }

            .navbar__brand {
                font-size: 1.1rem;
            }

            .navbar__user {
                display: none;
            }

            .navbar__actions {
                gap: var(--spacing-sm);
            }

            .hero {
                padding: 60px var(--spacing);
            }

            .hero__title {
                font-size: 1.8rem;
            }

            .hero__subtitle {
                font-size: 1rem;
            }

            .container {
                padding: 0 var(--spacing);
            }

            .shop__content {
                padding: var(--spacing-lg);
            }

            .shop__toolbar {
                flex-wrap: wrap;
            }

            .products {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing);
            }

            .product-card {
                border-radius: 10px;
            }

            .product-info {
                padding: var(--spacing);
            }

            .product-name {
                font-size: 0.85rem;
            }

            .product-price {
                font-size: 1rem;
            }

            .shop__title {
                font-size: 1.6rem;
            }

            .shop__sort {
                width: 100%;
                margin-top: var(--spacing-sm);
            }

            .shop__sort-select {
                flex: 1;
            }

            .filter-drawer.active {
                display: flex;
            }
        }

        /* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
           RESPONSIVE - SMALL MOBILE (480px)
           ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */

        @media (max-width: 480px) {
            .navbar__container {
                padding: 0 var(--spacing);
            }

            .navbar__brand {
                font-size: 1rem;
            }

            .navbar__icon-btn {
                font-size: 1rem;
            }

            .hero {
                padding: 40px var(--spacing);
            }

            .hero__title {
                font-size: 1.4rem;
            }

            .products {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing-sm);
            }

            .product-info {
                padding: var(--spacing-sm);
            }

            .shop__content {
                padding: var(--spacing);
            }

            .shop__title {
                font-size: 1.3rem;
            }

            .shop__toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .product-image {
                aspect-ratio: 3 / 4;
            }
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar__container">
            <a href="home.php" class="navbar__brand">
                <i class="fas fa-shopping-bag"></i>E-Store
            </a>

            <div class="navbar__nav">
                <a href="home.php" class="navbar__link">Home</a>
                <a href="shop.php" class="navbar__link">Shop</a>
                <a href="#" class="navbar__link">Men</a>
                <a href="#" class="navbar__link">Women</a>
            </div>

            <div class="navbar__actions">
                <button class="navbar__icon-btn" title="Search">
                    <i class="fas fa-search"></i>
                </button>
                <button class="navbar__icon-btn" title="Wishlist">
                    <i class="fas fa-heart"></i>
                </button>
                <button class="navbar__icon-btn" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="navbar__cart-badge">0</span>
                </button>
                <div class="navbar__user">
                    <span><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="../auth/logout.php" class="navbar__logout">Sign out</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">Curated for Modern Elegance</h1>
            <p class="hero__subtitle">Discover timeless pieces designed for every season</p>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- Mobile category chips -->
        <div class="category-chips">
            <a href="shop.php" class="category-chip<?php echo !$categoryId ? ' active' : ''; ?>">All</a>
            <?php foreach ($featuredCategories as $cat): ?>
            <a href="?category=<?php echo (int)$cat['id']; ?>" class="category-chip<?php echo ($categoryId == $cat['id']) ? ' active' : ''; ?>">
                <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="shop">
            <!-- FILTERS SIDEBAR -->
            <aside class="filters">
                <h2 class="filters__title">
                    <i class="fas fa-sliders-h"></i>Filters
                </h2>

                <!-- Search Filter -->
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Search</label>
                        <span class="filter__toggle">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </div>
                    <div class="filter__content">
                        <form method="GET" style="margin-top: var(--spacing-sm);">
                            <input type="text" name="search" placeholder="Search products..." style="width: 100%; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-family: 'DM Sans', sans-serif; font-size: 0.9rem;" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                        </form>
                    </div>
                </div>

                <!-- Category Filter -->
                <?php if (!empty($categories)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Category</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="cat-<?php echo (int)$cat['id']; ?>" name="category" value="<?php echo (int)$cat['id']; ?>" <?php echo ($categoryId == $cat['id']) ? 'checked' : ''; ?>>
                            <label for="cat-<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Brand Filter -->
                <?php if (!empty($brands)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Brand</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <?php foreach ($brands as $b): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="brand-<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>" name="brand" value="<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($brand === $b['brand']) ? 'checked' : ''; ?>>
                            <label for="brand-<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Color Filter -->
                <?php if (!empty($colors)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Color</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <div class="color-swatches">
                            <?php foreach ($colors as $c): ?>
                            <button type="button" class="color-swatch" data-color="<?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>" style="background-color: <?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>;<?php echo ($color === $c['color']) ? ' border-color: var(--color-accent);' : ''; ?>" title="<?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Size Filter -->
                <?php if (!empty($sizes)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Size</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <div class="size-pills">
                            <?php foreach ($sizes as $s): ?>
                            <button type="button" class="size-pill<?php echo ($size === $s['size']) ? ' active' : ''; ?>" data-size="<?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Price Filter -->
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Price</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <div class="filter-price-inputs">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars($minPrice, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars($maxPrice, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>
            </aside>

            <!-- MAIN SHOP CONTENT -->
            <main class="shop__content">
                <!-- Toolbar -->
                <div class="shop__toolbar">
                    <div class="shop__meta">
                        <h2 class="shop__title">Collection</h2>
                        <span class="shop__count"><?php echo $totalProducts; ?> Items Available</span>
                    </div>

                    <div class="shop__sort">
                        <label for="sort-select" class="shop__sort-label">Sort by:</label>
                        <select id="sort-select" class="shop__sort-select" onchange="location.href='?sort=' + this.value + (window.location.search ? '&' + window.location.search.substring(1).replace(/sort=[^&]*/gi, '') : '');">
                            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>
                            <option value="price-asc" <?php echo ($sort === 'price-asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-desc" <?php echo ($sort === 'price-desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="bestselling" <?php echo ($sort === 'bestselling') ? 'selected' : ''; ?>>Best Selling</option>
                            <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>Name: A-Z</option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters -->
                <?php
                $activeFilters = [];
                if ($search) $activeFilters[] = ['name' => 'Search', 'value' => $search];
                if ($categoryId) {
                    $catName = array_column($categories, 'name', 'id')[$categoryId] ?? 'Category';
                    $activeFilters[] = ['name' => 'Category', 'value' => $catName];
                }
                if ($brand) $activeFilters[] = ['name' => 'Brand', 'value' => $brand];
                if ($color) $activeFilters[] = ['name' => 'Color', 'value' => $color];
                if ($size) $activeFilters[] = ['name' => 'Size', 'value' => $size];
                if ($minPrice) $activeFilters[] = ['name' => 'MinPrice', 'value' => '$' . number_format($minPrice, 2)];
                if ($maxPrice) $activeFilters[] = ['name' => 'MaxPrice', 'value' => '$' . number_format($maxPrice, 2)];
                ?>

                <?php if (!empty($activeFilters)): ?>
                <div class="active-filters">
                    <?php foreach ($activeFilters as $filter): ?>
                    <span class="filter-tag">
                        <?php echo htmlspecialchars($filter['value'], ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" onclick="removeFilter('<?php echo htmlspecialchars($filter['name'], ENT_QUOTES, 'UTF-8'); ?>')">×</button>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Products Grid -->
                <?php if (count($products) === 0): ?>
                    <div class="products">
                        <div class="empty-state">
                            <div class="empty-state__icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3 class="empty-state__title">No Products Found</h3>
                            <p class="empty-state__description">We couldn't find any products matching your selection. Try adjusting your filters or explore our full collection.</p>
                            <a href="shop.php" class="empty-state__cta">View All Products</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="products">
                        <?php foreach ($products as $index => $product): ?>
                        <article class="product-card" style="--idx: <?php echo $index + 1; ?>;">
                            <!-- Image Section -->
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="product-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Badge -->
                                <?php if ((int)$product['stock'] === 0): ?>
                                <div class="product-badge">Out of Stock</div>
                                <?php endif; ?>

                                <!-- Wishlist Button -->
                                <button class="product-wishlist" title="Add to Wishlist" aria-label="Add to Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>

                                <!-- Quick Add Button -->
                                <?php if ((int)$product['stock'] > 0): ?>
                                <form method="POST" action="cart_action.php" style="display: contents;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                    <button type="submit" class="product-quick-add">Quick Add to Bag</button>
                                </form>
                                <?php endif; ?>
                            </div>

                            <!-- Product Info -->
                            <div class="product-info">
                                <span class="product-brand">
                                    <?php echo htmlspecialchars($product['brand'] ?? $product['category_name'] ?? 'Brand', ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo (int)$product['id']; ?>" style="color: inherit; text-decoration: none;">
                                        <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h3>

                                <!-- Color Dots -->
                                <?php if (!empty($product['color'])): ?>
                                <div class="product-colors">
                                    <div class="product-color-dot" style="background-color: <?php echo htmlspecialchars($product['color'], ENT_QUOTES, 'UTF-8'); ?>;"></div>
                                </div>
                                <?php endif; ?>

                                <!-- Price -->
                                <div class="product-price">
                                    $<?php echo number_format((float)$product['price'], 2); ?>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link">← Previous</a>
                        <?php else: ?>
                        <span class="pagination__link disabled">← Previous</span>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        
                        if ($startPage > 1): ?>
                            <a href="?page=1&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link">1</a>
                            <?php if ($startPage > 2): ?>
                            <span class="pagination__link">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                            <?php if ($p === $page): ?>
                            <span class="pagination__link active"><?php echo $p; ?></span>
                            <?php else: ?>
                            <a href="?page=<?php echo $p; ?>&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link"><?php echo $p; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination__link">...</span>
                            <?php endif; ?>
                            <a href="?page=<?php echo $totalPages; ?>&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link"><?php echo $totalPages; ?></a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link">Next →</a>
                        <?php else: ?>
                        <span class="pagination__link disabled">Next →</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- MOBILE FILTER DRAWER -->
    <div class="filter-drawer" id="filterDrawer">
        <div class="filter-drawer__header">
            <h3 class="filter-drawer__title">Filters</h3>
            <button class="filter-drawer__close" onclick="closeFilterDrawer()">✕</button>
        </div>
        <div class="filter-drawer__content" id="filterDrawerContent"></div>
        <div class="filter-drawer__actions">
            <button class="filter-drawer__btn filter-drawer__apply" onclick="applyMobileFilters()">Apply</button>
            <button class="filter-drawer__btn filter-drawer__reset" onclick="resetMobileFilters()">Reset</button>
        </div>
    </div>

    <!-- MOBILE BOTTOM NAVIGATION -->
    <nav class="bottom-nav">
        <div class="bottom-nav__content">
            <a href="home.php" class="bottom-nav__item">
                <span class="bottom-nav__icon"><i class="fas fa-home"></i></span>
                <span>Home</span>
            </a>
            <a href="shop.php" class="bottom-nav__item active">
                <span class="bottom-nav__icon"><i class="fas fa-shopping-bag"></i></span>
                <span>Shop</span>
            </a>
            <a href="cart.php" class="bottom-nav__item">
                <span class="bottom-nav__icon"><i class="fas fa-shopping-cart"></i></span>
                <span>Cart</span>
            </a>
            <a href="#" class="bottom-nav__item">
                <span class="bottom-nav__icon"><i class="fas fa-user"></i></span>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- FLOATING CART BUTTON -->
    <button class="floating-cart" onclick="location.href='cart.php';" title="View Cart">
        <i class="fas fa-shopping-bag"></i>
        <span class="floating-cart__badge">0</span>
    </button>

    <script>
        // ═══════════════════════════════════════════════════════════════════
        // NAVBAR SCROLL BEHAVIOR
        // ═══════════════════════════════════════════════════════════════════

        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar--shrink');
            } else {
                navbar.classList.remove('navbar--shrink');
            }
        });

        // ═══════════════════════════════════════════════════════════════════
        // FILTER ACCORDION
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.filter__header').forEach(header => {
            header.addEventListener('click', function() {
                this.closest('.filter').classList.toggle('collapsed');
            });
        });

        // ═══════════════════════════════════════════════════════════════════
        // WISHLIST
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.product-wishlist').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
            });
        });

        // ═══════════════════════════════════════════════════════════════════
        // SIZE PILLS
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.size-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                document.querySelectorAll('.size-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ═══════════════════════════════════════════════════════════════════
        // MOBILE FILTER DRAWER
        // ═══════════════════════════════════════════════════════════════════

        function openFilterDrawer() {
            document.getElementById('filterDrawer').classList.add('active');
        }

        function closeFilterDrawer() {
            document.getElementById('filterDrawer').classList.remove('active');
        }

        function applyMobileFilters() {
            closeFilterDrawer();
        }

        function resetMobileFilters() {
            location.href = 'shop.php';
        }

        // ═══════════════════════════════════════════════════════════════════
        // REMOVE FILTER TAG
        // ═══════════════════════════════════════════════════════════════════

        function removeFilter(filterName) {
            let url = new URL(window.location);
            switch(filterName) {
                case 'Search': url.searchParams.delete('search'); break;
                case 'Category': url.searchParams.delete('category'); break;
                case 'Brand': url.searchParams.delete('brand'); break;
                case 'Color': url.searchParams.delete('color'); break;
                case 'Size': url.searchParams.delete('size'); break;
                case 'MinPrice': url.searchParams.delete('min_price'); break;
                case 'MaxPrice': url.searchParams.delete('max_price'); break;
            }
            window.location = url.toString();
        }

        // ═══════════════════════════════════════════════════════════════════
        // COLOR SWATCHES
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.addEventListener('click', function() {
                document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
