<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = Database::connection();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Redirect admin to dashboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

// Get featured products
$featuredProducts = [];
$stmt = $pdo->query("
    SELECT id, name, price, image, stock, description, category_id
    FROM products 
    WHERE is_active = 1 
    ORDER BY views DESC, created_at DESC 
    LIMIT 8
");
$featuredProducts = $stmt->fetchAll();

// Get categories for quick access
$categories = [];
$stmt = $pdo->query("SELECT id, name FROM categories LIMIT 6");
$categories = $stmt->fetchAll();

function excerpt($text, $limit = 100) {
    $text = trim((string)$text);
    if ($text === '') {
        return 'Premium quality product.';
    }
    $text = preg_replace('/\s+/', ' ', $text);
    if (strlen($text) <= $limit) {
        return $text;
    }
    return substr($text, 0, $limit - 3) . '...';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Store - Premium Online Shopping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: var(--bg);">
    <!-- Navigation -->
    <nav class="navbar-modern">
        <div class="container">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <a class="navbar-brand-custom" href="home.php">
                    <i class="fas fa-shopping-bag me-2"></i>E-Store
                </a>
                <div class="nav-links">
                    <a href="shop.php" class="nav-link-item">Shop</a>
                    <a href="orders.php" class="nav-link-item">Orders</a>
                </div>
                <div class="nav-user-section">
                    <span class="user-greeting">Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="cart.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">
                        <i class="fas fa-shopping-cart me-1"></i>Cart
                    </a>
                    <a href="../auth/logout.php" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.9rem;">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container" style="position: relative; z-index: 2;">
            <h1>Welcome to E-Store</h1>
            <p>Discover premium products with exclusive collections and unbeatable prices</p>
            <a href="shop.php" class="btn btn-primary" style="padding: 14px 32px; font-size: 1.05rem; font-weight: 600;">
                <i class="fas fa-shopping-cart me-2"></i>Start Shopping
            </a>
        </div>
    </section>

    <!-- Quick Categories -->
    <div class="container" style="margin-top: 60px; margin-bottom: 80px;">
        <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 32px; text-align: center;">
            Browse by Category
        </h2>
        <?php if (!empty($categories)): ?>
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="shop.php?category=<?php echo (int)$category['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="category-card">
                            <div class="category-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="category-name">
                                <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Featured Products -->
    <div class="container" style="margin-bottom: 80px;">
        <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 32px; text-align: center;">
            Featured Products
        </h2>

        <?php if (empty($featuredProducts)): ?>
            <div class="empty-state" style="padding: 40px 0;">
                <div class="empty-state-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <h3>No Products Available</h3>
                <p>Check back soon for amazing products!</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
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
                            <div class="product-title">
                                <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>

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
                                <span style="color: var(--text-light); font-size: 0.85rem;">(4.5/5)</span>
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
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-shopping-cart me-1"></i>Add
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>
                                        <i class="fas fa-ban me-1"></i>Out
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <a href="shop.php" class="btn btn-primary" style="padding: 12px 28px; font-size: 1rem;">
                <i class="fas fa-list me-2"></i>View All Products
            </a>
        </div>
    </div>

    <!-- Promo Section -->
    <section style="background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%); color: white; padding: 60px 20px; margin: 80px 0; border-radius: 12px;">
        <div class="container" style="text-align: center;">
            <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 16px;">
                Exclusive Offers
            </h2>
            <p style="font-size: 1.1rem; margin-bottom: 24px; opacity: 0.95;">
                Get up to 50% off on selected premium products
            </p>
            <a href="shop.php" class="btn" style="background: white; color: var(--primary); padding: 12px 28px; font-weight: 600;">
                Shop Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--bg-light); border-top: 1px solid var(--border); padding: 60px 0 20px;">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 32px; margin-bottom: 40px;">
                <div>
                    <h4 style="font-weight: 700; margin-bottom: 16px; color: var(--primary);">
                        <i class="fas fa-shopping-bag me-2"></i>E-Store
                    </h4>
                    <p style="color: var(--text-light); line-height: 1.6;">
                        Your premium online shopping destination for quality products and exceptional service.
                    </p>
                </div>
                <div>
                    <h4 style="font-weight: 700; margin-bottom: 16px;">Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 12px;">
                            <a href="home.php" style="color: var(--text-light); text-decoration: none; transition: var(--transition);">Home</a>
                        </li>
                        <li style="margin-bottom: 12px;">
                            <a href="shop.php" style="color: var(--text-light); text-decoration: none; transition: var(--transition);">Shop</a>
                        </li>
                        <li style="margin-bottom: 12px;">
                            <a href="cart.php" style="color: var(--text-light); text-decoration: none; transition: var(--transition);">Cart</a>
                        </li>
                        <li style="margin-bottom: 12px;">
                            <a href="orders.php" style="color: var(--text-light); text-decoration: none; transition: var(--transition);">Orders</a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 style="font-weight: 700; margin-bottom: 16px;">Support</h4>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        <i class="fas fa-envelope me-2"></i>support@estore.com<br>
                        <i class="fas fa-phone me-2"></i>+1 (555) 123-4567<br>
                        <i class="fas fa-clock me-2"></i>24/7 Customer Service
                    </p>
                </div>
            </div>
            <div style="border-top: 1px solid var(--border); padding-top: 24px; text-align: center; color: var(--text-light);">
                <p>&copy; 2026 E-Store. All rights reserved. | Premium e-commerce platform</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
