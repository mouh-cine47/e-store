<?php
include '../includes/header.php';
include '../includes/sidebar.php';
$pdo = Database::connection();

$search = trim($_GET['search'] ?? '');
$params = [];
$filters = [];

if ($search !== '') {
    $filters[] = '(p.name LIKE :search OR p.brand LIKE :search OR c.name LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$whereSql = '';
if (count($filters) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $filters);
}

$tableStmt = $pdo->query("SHOW TABLES LIKE 'categories'");
$hasCategories = (bool)$tableStmt->fetch();

$columnStmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'");
$hasStock = (bool)$columnStmt->fetch();

$stockField = $hasStock ? 'p.stock' : 'p.quantity';
$brandField = $hasStock ? 'p.brand' : 'NULL';
$activeField = $hasStock ? 'p.is_active' : '1';

if ($hasCategories) {
    $sql = 'SELECT p.id, p.name, p.price, ' . $stockField . ' AS stock, ' . $activeField . ' AS is_active, '
        . $brandField . ' AS brand, c.name AS category_name '
        . 'FROM products p '
        . 'LEFT JOIN categories c ON c.id = p.category_id '
        . $whereSql . ' '
        . 'ORDER BY p.id DESC';
} else {
    $sql = 'SELECT p.id, p.name, p.price, ' . $stockField . ' AS stock, ' . $activeField . ' AS is_active, '
        . $brandField . ' AS brand, NULL AS category_name '
        . 'FROM products p '
        . $whereSql . ' '
        . 'ORDER BY p.id DESC';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
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
            <div class="table-responsive">
                <table class="table table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Stock Status</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?php echo (int)$row['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['brand'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>$<?php echo number_format((float)$row['price'], 2); ?></td>
                            <td><?php echo (int)$row['stock']; ?></td>
                            <td>
                                <?php if((int)$row['stock'] <= 0): ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php elseif((int)$row['stock'] < 5): ?>
                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-success">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$row['is_active'] === 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i></a>
                                <a href="delete.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
