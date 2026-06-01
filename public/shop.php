<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../includes/csrf.php';
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar-modern">
        <div class="container">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <a class="navbar-brand-custom" href="shop.php">
                    <i class="fas fa-shopping-bag me-2"></i>E-Store
                </a>
                <div class="nav-user-section">
                    <span class="user-greeting">Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="search_by_image.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;" title="Search by Image">
                        <i class="fas fa-camera me-1"></i>Search Image
                    </a>
                    <a href="cart.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">
                        <i class="fas fa-shopping-cart me-1"></i>Cart
                    </a>
                    <form method="POST" action="../auth/logout.php" style="display: inline;">
                        <?php csrf_field(); ?>
                        <button type="submit" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 32px; margin-bottom: 60px;">
        <?php if (!$hasProducts): ?>
            <div class="alert alert-warning" style="margin-bottom: 24px;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Products table is missing. Import database.sql to browse products.
            </div>
        <?php endif; ?>
        <?php if (!$hasCategories): ?>
            <div class="alert alert-warning" style="margin-bottom: 24px;">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Categories table is missing. Import database.sql to browse categories.
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 32px;">
            <!-- Filters Sidebar -->
            <aside>
                <div class="filter-section">
                    <h3 class="filter-title">
                        <i class="fas fa-sliders-h me-2"></i>Filters
                    </h3>
                    <form method="GET">
                        <!-- Search -->
                        <div class="filter-group">
                            <label class="form-label">Search Products</label>
                            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <!-- Category -->
                        <div class="filter-group">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo (int)$category['id']; ?>" <?php echo ($categoryId == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Brand -->
                        <div class="filter-group">
                            <label class="form-label">Brand</label>
                            <select name="brand" class="form-select">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $brandRow): ?>
                                    <option value="<?php echo htmlspecialchars($brandRow['brand'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($brand === $brandRow['brand']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brandRow['brand'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Color -->
                        <div class="filter-group">
                            <label class="form-label">Color</label>
                            <select name="color" class="form-select">
                                <option value="">All Colors</option>
                                <?php foreach ($colors as $colorRow): ?>
                                    <option value="<?php echo htmlspecialchars($colorRow['color'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($color === $colorRow['color']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($colorRow['color'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Size -->
                        <div class="filter-group">
                            <label class="form-label">Size</label>
                            <select name="size" class="form-select">
                                <option value="">All Sizes</option>
                                <?php foreach ($sizes as $sizeRow): ?>
                                    <option value="<?php echo htmlspecialchars($sizeRow['size'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($size === $sizeRow['size']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sizeRow['size'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Collection -->
                        <div class="filter-group">
                            <label class="form-label">Collection</label>
                            <select name="collection" class="form-select">
                                <option value="">All Collections</option>
                                <?php foreach ($collections as $collectionRow): ?>
                                    <option value="<?php echo htmlspecialchars($collectionRow['collection_name'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($collection === $collectionRow['collection_name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($collectionRow['collection_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="filter-group">
                            <label class="form-label">Price Range</label>
                            <div class="filter-price-inputs">
                                <input type="number" step="0.01" name="min_price" class="form-control" placeholder="Min" value="<?php echo htmlspecialchars($minPrice, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="number" step="0.01" name="max_price" class="form-control" placeholder="Max" value="<?php echo htmlspecialchars($maxPrice, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Products Section -->
            <main>
                <div class="shop-header">
                    <div>
                        <h2><?php echo $sectionLabel !== '' ? ucfirst($sectionLabel) . ' Collection' : 'All Products'; ?></h2>
                        <div class="shop-header-meta">
                            <i class="fas fa-box me-1"></i><?php echo count($products); ?> products found
                        </div>
                    </div>
                </div>

                <?php if (count($products) === 0): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <h3>No Products Found</h3>
                        <p>Try adjusting your filters to find what you're looking for.</p>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image-container">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-badge">
                                        <?php if ((int)$product['stock'] <= 0): ?>
                                            <span class="badge" style="background: var(--danger);">
                                                <i class="fas fa-times-circle me-1"></i>Out of Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: var(--success);">
                                                <i class="fas fa-check-circle me-1"></i>In Stock
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="product-info">
                                    <div class="product-category">
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>

                                    <h3 class="product-title">
                                        <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h3>

                                    <div class="product-price">
                                        $<?php echo number_format($product['price'], 2); ?>
                                    </div>

                                    <div class="product-rating">
                                        <span class="product-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </span>
                                        <span style="color: var(--text-light);">(124 reviews)</span>
                                    </div>

                                    <p class="product-desc">
                                        <?php echo htmlspecialchars(excerpt($product['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </p>

                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-outline" style="flex: 1;">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <?php if ((int)$product['stock'] > 0): ?>
                                            <form method="POST" action="cart_action.php" style="flex: 1;">
                                                <?php csrf_field(); ?>
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="fas fa-shopping-cart me-1"></i>Add
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="fas fa-ban me-1"></i>Unavailable
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <footer style="background: var(--bg-light); border-top: 1px solid var(--border); padding: 40px 0; margin-top: 60px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 32px;">
                <div>
                    <h4 style="font-weight: 700; margin-bottom: 16px;">About E-Store</h4>
                    <p style="color: var(--text-light); line-height: 1.6;">Your premium online shopping destination for quality products and seamless experience.</p>
                </div>
                <div>
                    <h4 style="font-weight: 700; margin-bottom: 16px;">Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 8px;"><a href="shop.php" style="color: var(--text-light); text-decoration: none;">Shop</a></li>
                        <li style="margin-bottom: 8px;"><a href="cart.php" style="color: var(--text-light); text-decoration: none;">Cart</a></li>
                        <li style="margin-bottom: 8px;"><a href="orders.php" style="color: var(--text-light); text-decoration: none;">Orders</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="font-weight: 700; margin-bottom: 16px;">Customer Support</h4>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Email: support@estore.com<br>
                        Phone: +1 (555) 123-4567
                    </p>
                </div>
            </div>
            <div style="border-top: 1px solid var(--border); padding-top: 24px; margin-top: 32px; text-align: center; color: var(--text-light);">
                <p>&copy; 2026 E-Store. All rights reserved. | Premium e-commerce platform</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
