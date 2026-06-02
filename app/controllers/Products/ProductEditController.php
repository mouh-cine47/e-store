<?php

class ProductEditController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
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
            include project_path('includes/footer.php');
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
            if (!csrf_validate()) {
                $error = 'Invalid form token. Please refresh and try again.';
            } else {
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
        }
        $this->render('products/edit', get_defined_vars());
    }
}
