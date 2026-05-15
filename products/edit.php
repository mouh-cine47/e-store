<?php
include '../includes/header.php';
include '../includes/sidebar.php';
$pdo = Database::connection();

$productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
$hasProducts = (bool)$productsTableStmt->fetch();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = '';
$error = '';

function slugify($text)
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text === '' ? 'product' : $text;
}

function uniqueSlug(PDO $pdo, $table, $slug, $excludeId = null)
{
    $base = $slug;
    $suffix = 1;

    while (true) {
        $checkSql = 'SELECT id FROM ' . $table . ' WHERE slug = :slug';
        $params = ['slug' => $slug];
        if ($excludeId !== null) {
            $checkSql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }
        $stmt = $pdo->prepare($checkSql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $suffix++;
        $slug = $base . '-' . $suffix;
    }
}

$productStmt = null;
$product = null;
if ($hasProducts) {
    $productStmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
    $productStmt->execute(['id' => $id]);
    $product = $productStmt->fetch();
}

if (!$hasProducts) {
    echo '<div class="container-fluid">';
    echo '<div class="alert alert-warning">Products table is missing. Import database.sql to edit products.</div>';
    echo '</div>';
    include '../includes/footer.php';
    exit();
}

if (!$product) {
    header('Location: index.php?error=not_found');
    exit();
}

$categories = [];
$hasCategories = false;
$tableStmt = $pdo->query("SHOW TABLES LIKE 'categories'");
if ($tableStmt->fetch()) {
    $hasCategories = true;
    $categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
    $categories = $categoriesStmt->fetchAll();
}

$columnStmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'");
$hasStock = (bool)$columnStmt->fetch();
$isLegacySchema = !$hasStock;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $categoryId = trim($_POST['category_id'] ?? '');
    $newCategory = trim($_POST['new_category'] ?? '');
    $categoryText = trim($_POST['category'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $collectionName = trim($_POST['collection_name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '0');
    $quantity = trim($_POST['quantity'] ?? '0');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $imagePath = $product['image'] ?? null;
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Product name is required.';
    } elseif ($price === '' || !is_numeric($price)) {
        $error = 'Please enter a valid price.';
    } elseif ($isLegacySchema) {
        if ($categoryText === '') {
            $error = 'Category is required for the legacy schema.';
        } elseif ($quantity === '' || !is_numeric($quantity) || (int)$quantity < 0) {
            $error = 'Please enter a valid quantity value.';
        }
    } elseif ($stock === '' || !is_numeric($stock) || (int)$stock < 0) {
        $error = 'Please enter a valid stock value.';
    } else {
        if ($isLegacySchema) {
            $stmt = $pdo->prepare(
                'UPDATE products SET name = :name, category = :category, price = :price, quantity = :quantity WHERE id = :id'
            );
            $stmt->execute([
                'name' => $name,
                'category' => $categoryText,
                'price' => (float)$price,
                'quantity' => (int)$quantity,
                'id' => $product['id'],
            ]);
            $success = 'Product updated successfully (legacy schema).';
        } else {
            $resolvedCategoryId = null;
            if ($hasCategories) {
                if ($newCategory !== '') {
                    $categorySlug = uniqueSlug($pdo, 'categories', slugify($newCategory));
                    $categoryStmt = $pdo->prepare('SELECT id FROM categories WHERE name = :name LIMIT 1');
                    $categoryStmt->execute(['name' => $newCategory]);
                    $existingCategory = $categoryStmt->fetch();
                    if ($existingCategory) {
                        $resolvedCategoryId = (int)$existingCategory['id'];
                    } else {
                        $insertCategory = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (:name, :slug)');
                        $insertCategory->execute([
                            'name' => $newCategory,
                            'slug' => $categorySlug,
                        ]);
                        $resolvedCategoryId = (int)$pdo->lastInsertId();
                    }
                } elseif ($categoryId !== '') {
                    $resolvedCategoryId = (int)$categoryId;
                }
            }

            $slug = uniqueSlug($pdo, 'products', slugify($name), $product['id']);
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $_FILES['image_file'];
                if ($upload['error'] !== UPLOAD_ERR_OK) {
                    $error = 'Failed to upload product image.';
                } elseif ($upload['size'] > 2 * 1024 * 1024) {
                    $error = 'Product image must be 2MB or less.';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($upload['tmp_name']);
                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                    ];

                    if (!isset($allowed[$mime])) {
                        $error = 'Product image must be JPG, PNG, WEBP, or GIF.';
                    } else {
                        $uploadDir = __DIR__ . '/../public/uploads/products';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
                        $targetPath = $uploadDir . '/' . $filename;

                        if (!move_uploaded_file($upload['tmp_name'], $targetPath)) {
                            $error = 'Unable to save the product image.';
                        } else {
                            if (!empty($imagePath) && strpos($imagePath, 'uploads/products/') === 0) {
                                $oldPath = __DIR__ . '/../public/' . $imagePath;
                                if (is_file($oldPath)) {
                                    unlink($oldPath);
                                }
                            }
                            $imagePath = 'uploads/products/' . $filename;
                        }
                    }
                }
            }

            if ($error === '') {
                $stmt = $pdo->prepare(
                    'UPDATE products SET name = :name, slug = :slug, description = :description, brand = :brand, '
                    . 'color = :color, size = :size, collection_name = :collection_name, category_id = :category_id, '
                    . 'price = :price, stock = :stock, image = :image, is_active = :is_active '
                    . 'WHERE id = :id'
                );
                $stmt->execute([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description !== '' ? $description : null,
                    'brand' => $brand !== '' ? $brand : null,
                    'color' => $color !== '' ? $color : null,
                    'size' => $size !== '' ? $size : null,
                    'collection_name' => $collectionName !== '' ? $collectionName : null,
                    'category_id' => $resolvedCategoryId,
                    'price' => (float)$price,
                    'stock' => (int)$stock,
                    'image' => $imagePath,
                    'is_active' => $isActive,
                    'id' => $product['id'],
                ]);

                $success = 'Product updated successfully!';
            }
        }

        $productStmt->execute(['id' => $id]);
        $product = $productStmt->fetch();
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Edit Product</h1>

    <div class="card shadow mb-4 col-lg-6">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Details: <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h6>
        </div>
        <div class="card-body">
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if (!$hasCategories): ?>
                <div class="alert alert-warning">
                    Categories table is missing. Import the updated database.sql to enable categories.
                </div>
            <?php endif; ?>
            <?php if ($isLegacySchema): ?>
                <div class="alert alert-warning">
                    Legacy product schema detected. Only name, category, price, and quantity will be saved.
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <?php if ($hasCategories && !$isLegacySchema): ?>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo (int)$category['id']; ?>" <?php echo ((int)$product['category_id'] === (int)$category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Or add new category</label>
                        <input type="text" name="new_category" class="form-control" placeholder="New category">
                    </div>
                <?php endif; ?>
                <?php if ($isLegacySchema): ?>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($product['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                <?php endif; ?>
                <?php if (!$isLegacySchema): ?>
                <div class="mb-3">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($product['brand'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Color</label>
                        <input type="text" name="color" class="form-control" value="<?php echo htmlspecialchars($product['color'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control" value="<?php echo htmlspecialchars($product['size'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Collection</label>
                        <input type="text" name="collection_name" class="form-control" value="<?php echo htmlspecialchars($product['collection_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?php if ($isLegacySchema): ?>
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($product['quantity'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
                        <?php else: ?>
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="<?php echo htmlspecialchars($product['stock'], ENT_QUOTES, 'UTF-8'); ?>" min="0" required>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!$isLegacySchema): ?>
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <div class="form-text">JPG, PNG, WEBP, or GIF. Max 2MB.</div>
                    <?php if (!empty($product['image'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Product image" style="max-width: 140px;" class="img-thumbnail">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo ((int)$product['is_active'] === 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_active">Active product</label>
                </div>
                <?php endif; ?>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
