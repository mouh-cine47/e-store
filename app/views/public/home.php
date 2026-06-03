
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Store - Premium Online Shopping</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <script src="../assets/js/dark-mode.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar-modern">
        <div class="container">
            <div class="flex items-center justify-between">
                <a class="navbar-brand-custom" href="home.php">
                    <i class="fas fa-shopping-bag"></i> E-Store
                </a>
                <div class="nav-links">
                    <a href="shop.php" class="nav-link-item">Shop</a>
                    <a href="orders.php" class="nav-link-item">Orders</a>
                </div>
                <div class="nav-user-section flex items-center gap-4">
                    <span class="user-greeting">Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="cart.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-shopping-cart"></i> Cart
                    </a>
                    <button onclick="toggleDarkMode()" class="btn btn-outline btn-sm" title="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <form method="POST" action="../auth/logout.php" class="inline-flex items-center">
                        <?php csrf_field(); ?>
                        <button type="submit" class="btn btn-outline btn-sm">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container" style="position: relative; z-index: 2; text-align: center;">
            <h1>Welcome to E-Store</h1>
            <p class="text-lg text-secondary" style="margin-bottom: var(--spacing-8);">Discover premium products with exclusive collections and unbeatable prices</p>
            <div class="flex flex-wrap justify-center items-center gap-3">
                <a href="shop.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-cart"></i> Start Shopping
                </a>
                <button type="button" id="aiSearchBtn" class="btn btn-outline btn-lg">
                    <i class="fas fa-robot"></i> AI Smart Search
                </button>
            </div>
        </div>
    </section>

    <!-- Quick Categories -->
    <div class="container" style="margin-top: var(--spacing-20); margin-bottom: var(--spacing-20);">
        <h2 class="text-4xl font-bold text-center" style="margin-bottom: var(--spacing-8);">
            Browse by Category
        </h2>
        <?php if (!empty($categories)): ?>
            <div class="category-grid grid grid-cols-3 gap-6" style="margin-top: var(--spacing-8);">
                <?php foreach ($categories as $category): ?>
                    <a href="shop.php?category=<?php echo (int)$category['id']; ?>" class="category-card card hover:shadow-lg">
                        <div class="card-body text-center">
                            <div class="category-icon" style="font-size: var(--text-4xl); margin-bottom: var(--spacing-3);">
                                <i class="fas fa-tag" style="color: var(--color-primary);"></i>
                            </div>
                            <div class="category-name font-semibold">
                                <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Featured Products -->
    <div class="container" style="margin-bottom: var(--spacing-20);">
        <h2 class="text-4xl font-bold text-center" style="margin-bottom: var(--spacing-8);">
            Featured Products
        </h2>

        <?php if (empty($featuredProducts)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <h3>No Products Available</h3>
                <p>Check back soon for amazing products!</p>
            </div>
        <?php else: ?>
            <div class="products-grid grid grid-cols-3 gap-6">
                <?php foreach ($featuredProducts as $product): ?>
                    <div class="product-card card overflow-hidden">
                        <div class="product-image-container" style="height: 250px; overflow: hidden; background-color: var(--bg-secondary);">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-gray-400" style="height: 100%;">
                                    <i class="fas fa-image" style="font-size: var(--text-4xl);"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="font-semibold"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <?php if ((int)$product['stock'] <= 0): ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-success">In Stock</span>
                                <?php endif; ?>
                            </div>

                            <div class="text-primary font-bold mb-3">
                                $<?php echo number_format($product['price'], 2); ?>
                            </div>

                            <p class="text-sm text-secondary mb-4">
                                <?php echo htmlspecialchars(excerpt($product['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </p>

                            <div class="flex gap-2">
                                <a href="product.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-outline btn-sm flex-1">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ((int)$product['stock'] > 0): ?>
                                    <form method="POST" action="cart_action.php" class="flex-1">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-sm w-full">
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm flex-1" disabled>
                                        <i class="fas fa-ban"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="text-center" style="margin-top: var(--spacing-12);">
            <a href="shop.php" class="btn btn-primary btn-lg">
                <i class="fas fa-list"></i> View All Products
            </a>
        </div>
    </div>

    <!-- Promo Section -->
    <section class="card card-elevated" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); color: var(--text-inverse); padding: var(--spacing-20) var(--spacing-6); margin: var(--spacing-20) 0; border: none;">
        <div class="container text-center">
            <h2 class="text-4xl font-bold" style="color: var(--text-inverse); margin-bottom: var(--spacing-4);">
                Exclusive Offers
            </h2>
            <p class="text-lg" style="color: rgba(255, 255, 255, 0.9); margin-bottom: var(--spacing-8);">
                Get up to 50% off on selected premium products
            </p>
            <a href="shop.php" class="btn btn-white" style="background: var(--color-white); color: var(--color-primary);">
                Shop Now
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--bg-secondary); border-top: 1px solid var(--border-color); padding: var(--spacing-20) 0 var(--spacing-6);">
        <div class="container">
            <div class="grid grid-cols-3 gap-8 mb-12">
                <div>
                    <h4 class="font-bold mb-4 text-primary">
                        <i class="fas fa-shopping-bag"></i> E-Store
                    </h4>
                    <p class="text-secondary text-sm">
                        Your premium online shopping destination for quality products and exceptional service.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
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
                    <h4 class="font-bold mb-4">Support</h4>
                    <div class="text-secondary text-sm space-y-2">
                        <p><i class="fas fa-envelope"></i> support@estore.com</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-clock"></i> 24/7 Customer Service</p>
                    </div>
                </div>
            </div>
            <div class="border-top border-color pt-6 text-center text-tertiary text-sm">
                <p>&copy; 2026 E-Store. All rights reserved. | Premium e-commerce platform</p>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var aiSearchBtn = document.getElementById('aiSearchBtn');
            if (!aiSearchBtn) {
                return;
            }
            aiSearchBtn.addEventListener('click', function () {
                alert('AI Smart Search is coming soon. This placeholder will be connected to the search assistant shortly.');
            });
        });
    </script>
</body>
</html>
