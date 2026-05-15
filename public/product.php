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

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare(
    'SELECT p.id, p.name, p.description, p.price, p.stock, p.image, p.brand, c.name AS category_name '
    . 'FROM products p '
    . 'LEFT JOIN categories c ON c.id = p.category_id '
    . 'WHERE p.id = :id AND p.is_active = 1 '
    . 'LIMIT 1'
);
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if ($product) {
    if (!isset($_SESSION['viewed_products']) || !is_array($_SESSION['viewed_products'])) {
        $_SESSION['viewed_products'] = [];
    }

    if (!in_array($productId, $_SESSION['viewed_products'], true)) {
        $_SESSION['viewed_products'][] = $productId;
        $ip = Geo::getClientIp();
        $geo = Geo::lookup($ip);

        $viewStmt = $pdo->prepare(
            'INSERT INTO product_views (product_id, user_id, ip, city, country) '
            . 'VALUES (:product_id, :user_id, :ip, :city, :country)'
        );
        $viewStmt->execute([
            'product_id' => $productId,
            'user_id' => $_SESSION['user_id'],
            'ip' => $ip,
            'city' => $geo['city'],
            'country' => $geo['country'],
        ]);

        $pdo->prepare('UPDATE products SET views = views + 1 WHERE id = :id')->execute(['id' => $productId]);
    }
}

if (!$product) {
    http_response_code(404);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') : 'Product Not Found'; ?> - E-Store</title>
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
        <?php if (!$product): ?>
            <div class="alert alert-danger">Product not found.</div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-5 mb-4">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded shadow-sm" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                        <div class="bg-secondary-subtle d-flex align-items-center justify-content-center rounded" style="height: 320px;">
                            <span class="text-muted">No Image</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-7">
                    <h2><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="text-muted mb-1">Category: <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php if (!empty($product['brand'])): ?>
                        <p class="text-muted">Brand: <?php echo htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                    <div class="h4 text-primary mb-3">$<?php echo number_format($product['price'], 2); ?></div>
                    <?php if ((int)$product['stock'] <= 0): ?>
                        <span class="badge bg-danger mb-3">Out of stock</span>
                    <?php else: ?>
                        <span class="badge bg-success mb-3">In stock</span>
                    <?php endif; ?>
                    <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description provided.', ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php if ((int)$product['stock'] > 0): ?>
                        <form method="POST" action="cart_action.php" class="mb-3">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                            <div class="input-group" style="max-width: 220px;">
                                <input type="number" name="qty" class="form-control" min="1" max="<?php echo (int)$product['stock']; ?>" value="1">
                                <button type="submit" class="btn btn-primary">Add to Cart</button>
                            </div>
                        </form>
                    <?php endif; ?>
                    <a href="shop.php" class="btn btn-outline-secondary">Back to shop</a>
                </div>
             </div>
         <?php endif; ?>
     </div>
</body>
</html>
