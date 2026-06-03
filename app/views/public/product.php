<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($product) && $product ? htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') : 'Product'; ?> - E-Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <script src="../assets/js/dark-mode.js"></script>
</head>
<body>
    <nav class="navbar navbar-modern">
        <div class="navbar__top">
            <a href="home.php" class="navbar__brand">
                <i class="fas fa-shopping-bag"></i> E-Store
            </a>
            <div class="navbar__actions">
                <button onclick="toggleDarkMode()" class="btn btn-outline btn-sm" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="cart.php" class="navbar__cart" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="navbar__cart-badge"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </a>
                <?php if (!empty($_SESSION['user_id'])): ?>
                    <div class="navbar__user">
                        <span><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <form method="POST" action="../auth/logout.php" class="navbar__logout-form">
                            <?php csrf_field(); ?>
                            <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                        </form>
                    </div>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary btn-sm">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="navbar__bottom">
            <a href="home.php" class="navbar__link">Home</a>
            <a href="shop.php" class="navbar__link">Shop</a>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="orders.php" class="navbar__link">My Orders</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container py-8">
        <?php if (!isset($hasProducts) || !$hasProducts): ?>
            <div class="alert alert-warning">
                <h3>Products unavailable</h3>
                <p>The products table is missing. Import the database or connect the product catalog.</p>
            </div>
        <?php elseif (!isset($product) || !$product): ?>
            <div class="alert alert-danger">
                <h3>Product not found</h3>
                <p>We couldn't find the requested product. <a href="shop.php">Return to shop</a></p>
            </div>
        <?php else: ?>
            <div class="product-detail">
                <div class="product-detail__gallery">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" class="product-detail__image">
                    <?php else: ?>
                        <div class="product-detail__placeholder">
                            <i class="fas fa-image"></i>
                            <p>No image available</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="product-detail__info">
                    <div class="product-detail__header">
                        <p class="product-detail__brand"><?php echo htmlspecialchars($product['brand'] ?? 'E-Store Collection', ENT_QUOTES, 'UTF-8'); ?></p>
                        <h1 class="product-detail__title"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    </div>

                    <div class="product-detail__price">
                        <span class="price">$<?php echo number_format((float)$product['price'], 2); ?></span>
                        <span class="stock <?php echo ((int)$product['stock'] <= 0) ? 'out' : 'in'; ?>">
                            <?php echo ((int)$product['stock'] <= 0) ? 'Out of Stock' : 'In Stock'; ?>
                        </span>
                    </div>

                    <div class="product-detail__specs">
                        <?php if (!empty($product['category_name'])): ?>
                            <div class="spec">
                                <span class="spec-label">Category:</span>
                                <span class="spec-value"><?php echo htmlspecialchars($product['category_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['color'])): ?>
                            <div class="spec">
                                <span class="spec-label">Color:</span>
                                <span class="spec-value"><?php echo htmlspecialchars($product['color'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['size'])): ?>
                            <div class="spec">
                                <span class="spec-label">Size:</span>
                                <span class="spec-value"><?php echo htmlspecialchars($product['size'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-detail__description">
                        <h3>About this item</h3>
                        <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description provided.', ENT_QUOTES, 'UTF-8')); ?></p>
                    </div>

                    <div class="product-detail__actions">
                        <?php if ((int)$product['stock'] > 0): ?>
                            <form method="POST" action="cart_action.php" class="add-to-cart-form">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                <div class="qty-selector">
                                    <label for="qty">Quantity:</label>
                                    <select id="qty" name="qty" class="form-select">
                                        <?php for ($i = 1; $i <= min(10, (int)$product['stock']); $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <div class="out-of-stock-message">
                                <p>This product is currently out of stock. Check back soon!</p>
                            </div>
                        <?php endif; ?>
                        <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 E-Store. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>