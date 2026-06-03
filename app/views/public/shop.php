<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop — E-Store | Premium Fashion</title>
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
                <?php if (!empty($_SESSION['user_name'])): ?>
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
            <a href="shop.php" class="navbar__link <?php echo empty($isSectionPage) ? 'navbar__link--active' : ''; ?>">Shop</a>
            <a href="women.php" class="navbar__link <?php echo (!empty($section) && $section === 'women') ? 'navbar__link--active' : ''; ?>">Women</a>
            <a href="men.php" class="navbar__link <?php echo (!empty($section) && $section === 'men') ? 'navbar__link--active' : ''; ?>">Men</a>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <a href="orders.php" class="navbar__link">My Orders</a>
            <?php endif; ?>
        </div>
    </nav>

    <section class="page-hero">
        <div class="container">
            <h1><?php echo htmlspecialchars($sectionTitle ?? 'Shop Our Collection', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($sectionDescription ?? 'Discover modern fashion designed for every occasion', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </section>

    <main class="container py-8">
        <div class="shop-layout">
            <aside class="shop-filters">
                <div class="filters-card">
                    <h3 class="filters-title">Filters</h3>
                    <form method="GET" class="filters-form">
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input id="search" name="search" type="search" value="<?php echo htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Product name..." class="form-input">
                        </div>

                        <?php if (!empty($isSectionPage)): ?>
                            <div class="filter-group">
                                <label>Section</label>
                                <div class="category-chip active"><?php echo htmlspecialchars($sectionLabels[$section] ?? ucfirst($section), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php elseif (!empty($categories)): ?>
                            <div class="filter-group">
                                <label for="category">Category</label>
                                <select id="category" name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['id']; ?>"<?php echo (isset($categoryId) && $categoryId == $cat['id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($brands)): ?>
                            <div class="filter-group">
                                <label for="brand">Brand</label>
                                <select id="brand" name="brand" class="form-select">
                                    <option value="">All Brands</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo (isset($brand) && $brand === $b['brand']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($colors)): ?>
                            <div class="filter-group">
                                <label for="color">Color</label>
                                <select id="color" name="color" class="form-select">
                                    <option value="">All Colors</option>
                                    <?php foreach ($colors as $c): ?>
                                        <option value="<?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo (isset($color) && $color === $c['color']) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($sizes)): ?>
                            <div class="filter-group">
                                <label for="size">Size</label>
                                <select id="size" name="size" class="form-select">
                                    <option value="">All Sizes</option>
                                    <?php foreach ($sizes as $s): ?>
                                        <option value="<?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo (isset($size) && $size === $s['size']) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($collections)): ?>
                            <div class="filter-group">
                                <label for="collection">Collection</label>
                                <select id="collection" name="collection" class="form-select">
                                    <option value="">All Collections</option>
                                    <?php foreach ($collections as $col): ?>
                                        <option value="<?php echo htmlspecialchars($col['collection_name'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo (isset($collection) && $collection === $col['collection_name']) ? ' selected' : ''; ?>>
                                            <?php echo htmlspecialchars($col['collection_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="filter-group">
                            <label>Price Range</label>
                            <div class="price-inputs">
                                <input name="min_price" type="number" step="0.01" value="<?php echo htmlspecialchars($minPrice ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Min" class="form-input">
                                <input name="max_price" type="number" step="0.01" value="<?php echo htmlspecialchars($maxPrice ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Max" class="form-input">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">Apply Filters</button>
                        <button type="button" class="btn btn-outline w-full" id="aiSearchButton" title="AI-Powered Search">
                            <i class="fas fa-robot"></i> AI Search
                        </button>
                        <a href="<?php echo htmlspecialchars($clearUrl ?? 'shop.php', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline w-full">Clear All</a>
                    </form>
                </div>
            </aside>

            <section class="shop-content">
                <div class="shop-header">
                    <div>
                        <h2 class="shop-title"><?php echo !empty($isSectionPage) ? htmlspecialchars(($sectionLabels[$section] ?? ucfirst($section)) . ' Products', ENT_QUOTES, 'UTF-8') : 'Products'; ?></h2>
                        <p class="shop-count">Showing <?php echo isset($totalProducts) ? (int)$totalProducts : 0; ?> items</p>
                    </div>
                    <div class="shop-sort">
                        <label for="sort">Sort:</label>
                        <select id="sort" name="sort" class="form-select" onchange="location.href='?sort=' + encodeURIComponent(this.value) + window.location.search.replace(/([?&])sort=[^&]*/,'')">
                            <option value="newest"<?php echo (isset($sort) && $sort === 'newest') ? ' selected' : ''; ?>>Newest</option>
                            <option value="price-asc"<?php echo (isset($sort) && $sort === 'price-asc') ? ' selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-desc"<?php echo (isset($sort) && $sort === 'price-desc') ? ' selected' : ''; ?>>Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>No Products Found</h3>
                        <p>Try adjusting your filters or search terms</p>
                        <a href="<?php echo htmlspecialchars($clearUrl ?? 'shop.php', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline mt-4">Reset Filters</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $productItem): ?>
                            <a href="product.php?id=<?php echo (int)$productItem['id']; ?>" class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($productItem['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($productItem['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($productItem['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ((int)$productItem['stock'] === 0): ?>
                                        <span class="product-badge product-badge--out">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-info">
                                    <p class="product-brand"><?php echo htmlspecialchars($productItem['brand'] ?? 'E-Store', ENT_QUOTES, 'UTF-8'); ?></p>
                                    <h3 class="product-title"><?php echo htmlspecialchars($productItem['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <div class="product-footer">
                                        <span class="product-price">$<?php echo number_format((float)$productItem['price'], 2); ?></span>
                                        <?php if (!empty($productItem['collection_name'])): ?>
                                            <span class="product-collection"><?php echo htmlspecialchars($productItem['collection_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 E-Store. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var aiSearchButton = document.getElementById('aiSearchButton');
            if (aiSearchButton) {
                aiSearchButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    alert('🤖 AI Search feature is coming soon! Upload an image to find similar products.');
                });
            }
        });
    </script>
</body>
</html>

