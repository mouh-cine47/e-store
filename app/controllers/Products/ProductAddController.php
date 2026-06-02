<?php

class ProductAddController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
        $pdo = Database::connection();
        
        $productsTableStmt = $pdo->query("SHOW TABLES LIKE 'products'");
        $hasProducts = (bool)$productsTableStmt->fetch();
        
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
        
        $categories = [];
        $hasCategories = false;
        $tableStmt = $pdo->query("SHOW TABLES LIKE 'categories'");
        if ($tableStmt->fetch()) {
            $hasCategories = true;
            $categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
            $categories = $categoriesStmt->fetchAll();
        }
        
        $hasStock = false;
        $isLegacySchema = true;
        if ($hasProducts) {
            $columnStmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'");
            $hasStock = (bool)$columnStmt->fetch();
            $isLegacySchema = !$hasStock;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $error = 'Invalid form token. Please refresh and try again.';
            } elseif (!$hasProducts) {
                $error = 'Products table is missing. Import database.sql to add products.';
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
                $imagePath = null;
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
                            'INSERT INTO products (name, category, price, quantity) VALUES (:name, :category, :price, :quantity)'
                        );
                        $stmt->execute([
                            'name' => $name,
                            'category' => $categoryText,
                            'price' => (float)$price,
                            'quantity' => (int)$quantity,
                        ]);
                        $success = 'Product added successfully (legacy schema).';
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
        
                        $slug = uniqueSlug($pdo, 'products', slugify($name));
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
                                        $imagePath = 'uploads/products/' . $filename;
                                    }
                                }
                            }
                        }
        
                        if ($error === '') {
                            $stmt = $pdo->prepare(
                                'INSERT INTO products (name, slug, description, brand, color, size, collection_name, category_id, price, stock, image, is_active) '
                                . 'VALUES (:name, :slug, :description, :brand, :color, :size, :collection_name, :category_id, :price, :stock, :image, :is_active)'
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
                            ]);
        
                            $success = 'Product added successfully!';
                        }
                    }
                }
            }
        }
        $this->render('products/add', get_defined_vars());
    }
}
