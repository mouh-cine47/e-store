<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = Database::connection();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

$search = trim($_GET['search'] ?? '');
$categoryId = trim($_GET['category'] ?? '');
$brand = trim($_GET['brand'] ?? '');
$minPrice = trim($_GET['min_price'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');

$filters = [];
$params = [];

$filters[] = 'p.is_active = 1';

if ($search !== '') {
    $filters[] = '(p.name LIKE :search OR p.description LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

if ($categoryId !== '') {
    $filters[] = 'p.category_id = :category_id';
    $params['category_id'] = $categoryId;
}

if ($brand !== '') {
    $filters[] = 'p.brand = :brand';
    $params['brand'] = $brand;
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

$categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
$categories = $categoriesStmt->fetchAll();

$brandsStmt = $pdo->query('SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand <> "" ORDER BY brand');
$brands = $brandsStmt->fetchAll();

$sql = 'SELECT p.id, p.name, p.price, p.stock, p.image, c.name AS category_name '
    . 'FROM products p '
    . 'LEFT JOIN categories c ON c.id = p.category_id '
    . $whereSql . ' '
    . 'ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="shop.php">E-Store</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-secondary">Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="cart.php" class="btn btn-outline-primary btn-sm me-2">Cart</a>
                <a href="../auth/logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Filters</h5>
                        <form method="GET">
                            <div class="mb-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo (int)$category['id']; ?>" <?php echo ($categoryId == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <select name="brand" class="form-select">
                                    <option value="">All</option>
                                    <?php foreach ($brands as $brandRow): ?>
                                        <option value="<?php echo htmlspecialchars($brandRow['brand'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($brand === $brandRow['brand']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brandRow['brand'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Min Price</label>
                                <input type="number" step="0.01" name="min_price" class="form-control" value="<?php echo htmlspecialchars($minPrice, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Max Price</label>
                                <input type="number" step="0.01" name="max_price" class="form-control" value="<?php echo htmlspecialchars($maxPrice, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Apply</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="mb-0">Products</h4>
                    <span class="text-muted"><?php echo count($products); ?> items</span>
                </div>

                <div class="row">
                    <?php if (count($products) === 0): ?>
                        <div class="col-12">
                            <div class="alert alert-info">No products match your filters.</div>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-6 col-xl-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php else: ?>
                                    <div class="bg-secondary-subtle d-flex align-items-center justify-content-center" style="height: 180px;">
                                        <span class="text-muted">No Image</span>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title mb-1"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                    <small class="text-muted mb-2"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></small>
                                    <div class="mb-2 fw-semibold">$<?php echo number_format($product['price'], 2); ?></div>
                                    <?php if ((int)$product['stock'] <= 0): ?>
                                        <span class="badge bg-danger mb-3">Out of stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-success mb-3">In stock</span>
                                    <?php endif; ?>
                                    <div class="mt-auto">
                                        <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-outline-primary w-100 mb-2">View Details</a>
                                        <?php if ((int)$product['stock'] > 0): ?>
                                            <form method="POST" action="cart_action.php">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                                <input type="hidden" name="qty" value="1">
                                                <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
