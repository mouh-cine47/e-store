<?php
require_once __DIR__ . '/../app/bootstrap.php';

$controller = new PublicShopController();
$controller->index();
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">Curated for Modern Elegance</h1>
            <p class="hero__subtitle">Discover timeless pieces designed for every season</p>
        </div>
    </section>

    <!-- MAIN CONTENT -->
    <div class="container">
        <!-- Mobile category chips -->
        <div class="category-chips">
            <a href="shop.php" class="category-chip<?php echo !$categoryId ? ' active' : ''; ?>">All</a>
            <?php foreach ($featuredCategories as $cat): ?>
            <a href="?category=<?php echo (int)$cat['id']; ?>" class="category-chip<?php echo ($categoryId == $cat['id']) ? ' active' : ''; ?>">
                <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="shop">
            <!-- FILTERS SIDEBAR -->
            <aside class="filters">
                <h2 class="filters__title">
                    <i class="fas fa-sliders-h"></i>Filters
                </h2>

                <!-- Search Filter -->
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Search</label>
                        <span class="filter__toggle">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </div>
                    <div class="filter__content">
                        <form method="GET" style="margin-top: var(--spacing-sm);">
                            <input type="text" name="search" placeholder="Search products..." style="width: 100%; padding: var(--spacing-sm); border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-family: 'DM Sans', sans-serif; font-size: 0.9rem;" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                        </form>
                    </div>
                </div>

                <!-- Category Filter -->
                <?php if (!empty($categories)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Category</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="cat-<?php echo (int)$cat['id']; ?>" name="category" value="<?php echo (int)$cat['id']; ?>" <?php echo ($categoryId == $cat['id']) ? 'checked' : ''; ?>>
                            <label for="cat-<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Brand Filter -->
                <?php if (!empty($brands)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Brand</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <?php foreach ($brands as $b): ?>
                        <div class="filter-option">
                            <input type="checkbox" id="brand-<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>" name="brand" value="<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($brand === $b['brand']) ? 'checked' : ''; ?>>
                            <label for="brand-<?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($b['brand'], ENT_QUOTES, 'UTF-8'); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Color Filter -->
                <?php if (!empty($colors)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Color</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <div class="color-swatches">
                            <?php foreach ($colors as $c): ?>
                            <button type="button" class="color-swatch" data-color="<?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>" style="background-color: <?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>;<?php echo ($color === $c['color']) ? ' border-color: var(--color-accent);' : ''; ?>" title="<?php echo htmlspecialchars($c['color'], ENT_QUOTES, 'UTF-8'); ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Size Filter -->
                <?php if (!empty($sizes)): ?>
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Size</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <div class="size-pills">
                            <?php foreach ($sizes as $s): ?>
                            <button type="button" class="size-pill<?php echo ($size === $s['size']) ? ' active' : ''; ?>" data-size="<?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($s['size'], ENT_QUOTES, 'UTF-8'); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Price Filter -->
                <div class="filter">
                    <div class="filter__header">
                        <label class="filter__label">Price</label>
                        <span class="filter__toggle"><i class="fas fa-chevron-right"></i></span>
                    </div>
                    <div class="filter__content">
                        <div class="filter-price-inputs">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars($minPrice, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars($maxPrice, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>
            </aside>

            <!-- MAIN SHOP CONTENT -->
            <main class="shop__content">
                <!-- Toolbar -->
                <div class="shop__toolbar">
                    <div class="shop__meta">
                        <h2 class="shop__title">Collection</h2>
                        <span class="shop__count"><?php echo $totalProducts; ?> Items Available</span>
                    </div>

                    <div class="shop__sort">
                        <label for="sort-select" class="shop__sort-label">Sort by:</label>
                        <select id="sort-select" class="shop__sort-select" onchange="location.href='?sort=' + this.value + (window.location.search ? '&' + window.location.search.substring(1).replace(/sort=[^&]*/gi, '') : '');">
                            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>
                            <option value="price-asc" <?php echo ($sort === 'price-asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-desc" <?php echo ($sort === 'price-desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="bestselling" <?php echo ($sort === 'bestselling') ? 'selected' : ''; ?>>Best Selling</option>
                            <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>Name: A-Z</option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters -->
                <?php
                $activeFilters = [];
                if ($search) $activeFilters[] = ['name' => 'Search', 'value' => $search];
                if ($categoryId) {
                    $catName = array_column($categories, 'name', 'id')[$categoryId] ?? 'Category';
                    $activeFilters[] = ['name' => 'Category', 'value' => $catName];
                }
                if ($brand) $activeFilters[] = ['name' => 'Brand', 'value' => $brand];
                if ($color) $activeFilters[] = ['name' => 'Color', 'value' => $color];
                if ($size) $activeFilters[] = ['name' => 'Size', 'value' => $size];
                if ($minPrice) $activeFilters[] = ['name' => 'MinPrice', 'value' => '$' . number_format($minPrice, 2)];
                if ($maxPrice) $activeFilters[] = ['name' => 'MaxPrice', 'value' => '$' . number_format($maxPrice, 2)];
                ?>

                <?php if (!empty($activeFilters)): ?>
                <div class="active-filters">
                    <?php foreach ($activeFilters as $filter): ?>
                    <span class="filter-tag">
                        <?php echo htmlspecialchars($filter['value'], ENT_QUOTES, 'UTF-8'); ?>
                        <button type="button" onclick="removeFilter('<?php echo htmlspecialchars($filter['name'], ENT_QUOTES, 'UTF-8'); ?>')">×</button>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Products Grid -->
                <?php if (count($products) === 0): ?>
                    <div class="products">
                        <div class="empty-state">
                            <div class="empty-state__icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3 class="empty-state__title">No Products Found</h3>
                            <p class="empty-state__description">We couldn't find any products matching your selection. Try adjusting your filters or explore our full collection.</p>
                            <a href="shop.php" class="empty-state__cta">View All Products</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="products">
                        <?php foreach ($products as $index => $product): ?>
                        <article class="product-card" style="--idx: <?php echo $index + 1; ?>;">
                            <!-- Image Section -->
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>" loading="lazy">
                                <?php else: ?>
                                    <div class="product-image-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Badge -->
                                <?php if ((int)$product['stock'] === 0): ?>
                                <div class="product-badge">Out of Stock</div>
                                <?php endif; ?>

                                <!-- Wishlist Button -->
                                <button class="product-wishlist" title="Add to Wishlist" aria-label="Add to Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>

                                <!-- Quick Add Button -->
                                <?php if ((int)$product['stock'] > 0): ?>
                                <form method="POST" action="cart_action.php" style="display: contents;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                    <button type="submit" class="product-quick-add">Quick Add to Bag</button>
                                </form>
                                <?php endif; ?>
                            </div>

                            <!-- Product Info -->
                            <div class="product-info">
                                <span class="product-brand">
                                    <?php echo htmlspecialchars($product['brand'] ?? $product['category_name'] ?? 'Brand', ENT_QUOTES, 'UTF-8'); ?>
                                </span>

                                <h3 class="product-name">
                                    <a href="product.php?id=<?php echo (int)$product['id']; ?>" style="color: inherit; text-decoration: none;">
                                        <?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </h3>

                                <!-- Color Dots -->
                                <?php if (!empty($product['color'])): ?>
                                <div class="product-colors">
                                    <div class="product-color-dot" style="background-color: <?php echo htmlspecialchars($product['color'], ENT_QUOTES, 'UTF-8'); ?>;"></div>
                                </div>
                                <?php endif; ?>

                                <!-- Price -->
                                <div class="product-price">
                                    $<?php echo number_format((float)$product['price'], 2); ?>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- MOBILE FILTER DRAWER -->
    <div class="filter-drawer" id="filterDrawer">
        <div class="filter-drawer__header">
            <h3 class="filter-drawer__title">Filters</h3>
            <button class="filter-drawer__close" onclick="closeFilterDrawer()">✕</button>
        </div>
        <div class="filter-drawer__content" id="filterDrawerContent"></div>
        <div class="filter-drawer__actions">
            <button class="filter-drawer__btn filter-drawer__apply" onclick="applyMobileFilters()">Apply</button>
            <button class="filter-drawer__btn filter-drawer__reset" onclick="resetMobileFilters()">Reset</button>
        </div>
    </div>

    <!-- MOBILE BOTTOM NAVIGATION -->
    <nav class="bottom-nav">
        <div class="bottom-nav__content">
            <a href="home.php" class="bottom-nav__item">
                <span class="bottom-nav__icon"><i class="fas fa-home"></i></span>
                <span>Home</span>
            </a>
            <a href="shop.php" class="bottom-nav__item active">
                <span class="bottom-nav__icon"><i class="fas fa-shopping-bag"></i></span>
                <span>Shop</span>
            </a>
            <a href="cart.php" class="bottom-nav__item">
                <span class="bottom-nav__icon"><i class="fas fa-shopping-cart"></i></span>
                <span>Cart</span>
            </a>
            <a href="#" class="bottom-nav__item">
                <span class="bottom-nav__icon"><i class="fas fa-user"></i></span>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- FLOATING CART BUTTON -->
    <button class="floating-cart" onclick="location.href='cart.php';" title="View Cart">
        <i class="fas fa-shopping-bag"></i>
        <span class="floating-cart__badge">0</span>
    </button>

    <script>
        // ═══════════════════════════════════════════════════════════════════
        // NAVBAR SCROLL BEHAVIOR
        // ═══════════════════════════════════════════════════════════════════

        const navbar = document.querySelector('.navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar--shrink');
            } else {
                navbar.classList.remove('navbar--shrink');
            }
        });

        // ═══════════════════════════════════════════════════════════════════
        // FILTER ACCORDION
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.filter__header').forEach(header => {
            header.addEventListener('click', function() {
                this.closest('.filter').classList.toggle('collapsed');
            });
        });

        // ═══════════════════════════════════════════════════════════════════
        // WISHLIST
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.product-wishlist').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
            });
        });

        // ═══════════════════════════════════════════════════════════════════
        // SIZE PILLS
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.size-pill').forEach(pill => {
            pill.addEventListener('click', function() {
                document.querySelectorAll('.size-pill').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // ═══════════════════════════════════════════════════════════════════
        // MOBILE FILTER DRAWER
        // ═══════════════════════════════════════════════════════════════════

        function openFilterDrawer() {
            document.getElementById('filterDrawer').classList.add('active');
        }

        function closeFilterDrawer() {
            document.getElementById('filterDrawer').classList.remove('active');
        }

        function applyMobileFilters() {
            closeFilterDrawer();
        }

        function resetMobileFilters() {
            location.href = 'shop.php';
        }

        // ═══════════════════════════════════════════════════════════════════
        // REMOVE FILTER TAG
        // ═══════════════════════════════════════════════════════════════════

        function removeFilter(filterName) {
            let url = new URL(window.location);
            switch(filterName) {
                case 'Search': url.searchParams.delete('search'); break;
                case 'Category': url.searchParams.delete('category'); break;
                case 'Brand': url.searchParams.delete('brand'); break;
                case 'Color': url.searchParams.delete('color'); break;
                case 'Size': url.searchParams.delete('size'); break;
                case 'MinPrice': url.searchParams.delete('min_price'); break;
                case 'MaxPrice': url.searchParams.delete('max_price'); break;
            }
            window.location = url.toString();
        }

        // ═══════════════════════════════════════════════════════════════════
        // COLOR SWATCHES
        // ═══════════════════════════════════════════════════════════════════

        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.addEventListener('click', function() {
                document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>

