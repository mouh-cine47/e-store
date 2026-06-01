<?php
include '../includes/header.php';
include '../includes/sidebar.php';
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
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products Management</h1>
        <a href="add.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus fa-sm text-white-50"></i> Add New Product</a>
    </div>

    <?php if (!$hasCategories): ?>
        <div class="alert alert-warning">
            The categories table is missing. Import the updated database.sql to enable categories.
        </div>
    <?php endif; ?>
    <?php if (!$hasProducts): ?>
        <div class="alert alert-warning">
            The products table is missing. Import the updated database.sql to manage products.
        </div>
    <?php endif; ?>
    <?php if (!$hasStock): ?>
        <div class="alert alert-warning">
            The products table is still using legacy columns. Import the updated database.sql to enable stock, brand, and active fields.
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Products</h6>
            <form action="" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Search..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn btn-sm btn-primary">Search</button>
            </form>
        </div>
        <div class="card-body">
            <?php if (count($products) === 0): ?>
                <div class="alert alert-info">No products found.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($products as $row): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="card product-card h-100">
                                <div class="product-card-media">
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../public/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>" class="product-card-img">
                                    <?php else: ?>
                                        <div class="product-card-placeholder">No image</div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-start justify-content-between mb-2">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                        <?php if ((int)$row['is_active'] === 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-primary fw-semibold mb-2">$<?php echo number_format((float)$row['price'], 2); ?></div>
                                    <p class="product-card-desc text-muted mb-3"><?php echo htmlspecialchars(excerpt($row['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="mt-auto d-flex align-items-center justify-content-between">
                                        <span class="text-muted small">Stock: <?php echo (int)$row['stock']; ?></span>
                                        <?php if ((int)$row['stock'] <= 0): ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php elseif((int)$row['stock'] < 5): ?>
                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">In Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-0 d-flex">
                                    <a href="edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-info text-white me-2"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="delete.php" onsubmit="return confirm('Are you sure?');">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
