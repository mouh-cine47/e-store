

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product ? htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') : 'Product Not Found'; ?> - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Sora:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --page-bg: #f6f3ef;
            --card-bg: #ffffff;
            --text-main: #1c1b1f;
            --text-muted: #7a7f88;
            --line-soft: #e8e9ef;
            --accent: #111218;
            --accent-soft: #f1f2f6;
            --success: #1c7d56;
        }

        body.product-body {
            font-family: "Sora", "Helvetica Neue", sans-serif;
            background: linear-gradient(180deg, #f7f5f1 0%, #fbfaf8 45%, #ffffff 100%);
            color: var(--text-main);
            min-height: 100vh;
            position: relative;
        }

        body.product-body::before {
            content: "";
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(228, 224, 216, 0.6), transparent 50%),
                        radial-gradient(circle at 80% 10%, rgba(230, 232, 242, 0.5), transparent 45%);
            pointer-events: none;
            z-index: 0;
        }

        .page-shell {
            position: relative;
            z-index: 1;
            padding: 32px 0 60px;
        }

        .nav-shell {
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px solid var(--line-soft);
            backdrop-filter: blur(10px);
        }

        .brand-mark {
            font-family: "Playfair Display", "Times New Roman", serif;
            font-size: 1.5rem;
            color: var(--text-main);
            text-decoration: none;
            letter-spacing: 0.02em;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
        }

        .nav-actions .nav-pill {
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid var(--line-soft);
            color: var(--text-main);
            text-decoration: none;
            background: #fff;
        }

        .nav-user {
            color: var(--text-muted);
        }

        .product-header {
            font-size: 0.9rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .product-header a {
            color: var(--text-main);
            text-decoration: none;
        }

        .product-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: 32px;
        }

        .media-card {
            background: var(--card-bg);
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 20px 40px rgba(20, 24, 36, 0.12);
            border: 1px solid var(--line-soft);
            position: relative;
        }

        .media-frame {
            aspect-ratio: 4 / 5;
            border-radius: 14px;
            overflow: hidden;
            background: #f1f2f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-placeholder {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .wish-btn {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--line-soft);
            background: rgba(255, 255, 255, 0.95);
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .wish-btn span {
            font-size: 1rem;
        }

        .meta-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .meta-chip {
            background: var(--accent-soft);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
        }

        .brand-line {
            text-transform: uppercase;
            letter-spacing: 0.22em;
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .product-title {
            font-family: "Playfair Display", "Times New Roman", serif;
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .price-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 16px 0;
        }

        .price-tag {
            font-size: 1.6rem;
            font-weight: 600;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            background: #ecf7f1;
            color: var(--success);
        }

        .stock-badge.is-out {
            background: #fbecec;
            color: #b23b3b;
        }

        .stock-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .spec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .spec-item {
            background: var(--card-bg);
            border: 1px solid var(--line-soft);
            border-radius: 12px;
            padding: 10px 12px;
        }

        .spec-label {
            text-transform: uppercase;
            letter-spacing: 0.18em;
            font-size: 0.65rem;
            color: var(--text-muted);
        }

        .spec-value {
            font-weight: 600;
            margin-top: 4px;
        }

        .desc-card {
            background: var(--card-bg);
            border: 1px solid var(--line-soft);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 18px;
        }

        .desc-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .desc-text {
            color: #33363d;
            line-height: 1.6;
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        .qty-input {
            width: 90px;
            border-radius: 10px;
            border: 1px solid var(--line-soft);
            padding: 10px 12px;
        }

        .primary-btn {
            border: none;
            padding: 11px 18px;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
            font-weight: 500;
            min-width: 150px;
        }

        .ghost-btn {
            border: 1px solid var(--line-soft);
            padding: 10px 16px;
            border-radius: 999px;
            color: var(--text-main);
            text-decoration: none;
            background: #fff;
        }

        .trust-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 18px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .rise {
            opacity: 0;
            transform: translateY(12px);
            animation: rise 650ms ease forwards;
        }

        .delay-1 { animation-delay: 120ms; }
        .delay-2 { animation-delay: 240ms; }
        .delay-3 { animation-delay: 360ms; }

        @keyframes rise {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 992px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="product-body">
    <nav class="nav-shell">
        <div class="container py-3 d-flex align-items-center justify-content-between">
            <a class="brand-mark" href="shop.php">E-Store</a>
            <div class="nav-actions">
                <span class="nav-user">Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="cart.php" class="nav-pill">Cart</a>
                <form method="POST" action="../auth/logout.php" style="display: inline;">
                    <?php csrf_field(); ?>
                    <button type="submit" class="nav-pill">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container page-shell">
        <?php if (!$hasProducts): ?>
            <div class="alert alert-warning">Products table is missing. Import database.sql to view products.</div>
        <?php elseif (!$product): ?>
            <div class="alert alert-danger">Product not found.</div>
        <?php else: ?>
            <div class="product-header">
                <a href="shop.php">Shop</a>
                <span>/</span>
                <span><?php echo htmlspecialchars($product['category_name'] ?? 'All Products', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="product-grid">
                <div class="rise delay-1">
                    <div class="media-card">
                        <button class="wish-btn" type="button" aria-label="Add to wishlist">
                            <span>♡</span>
                        </button>
                        <div class="media-frame">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php else: ?>
                                <span class="media-placeholder">No Image</span>
                            <?php endif; ?>
                        </div>
                        <div class="meta-strip">
                            <span class="meta-chip"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if (!empty($product['collection_name'])): ?>
                                <span class="meta-chip"><?php echo htmlspecialchars($product['collection_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="rise delay-2">
                    <div class="brand-line"><?php echo htmlspecialchars($product['brand'] ?: 'E-Store Collection', ENT_QUOTES, 'UTF-8'); ?></div>
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></h1>

                    <div class="price-row">
                        <div class="price-tag">$<?php echo number_format($product['price'], 2); ?></div>
                        <div class="stock-badge <?php echo ((int)$product['stock'] <= 0) ? 'is-out' : ''; ?>">
                            <span class="stock-dot"></span>
                            <?php echo ((int)$product['stock'] <= 0) ? 'Out of stock' : 'In stock'; ?>
                        </div>
                    </div>

                    <div class="spec-grid">
                        <?php if (!empty($product['brand'])): ?>
                            <div class="spec-item">
                                <div class="spec-label">Brand</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['color'])): ?>
                            <div class="spec-item">
                                <div class="spec-label">Color</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['color'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['size'])): ?>
                            <div class="spec-item">
                                <div class="spec-label">Size</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['size'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['collection_name'])): ?>
                            <div class="spec-item">
                                <div class="spec-label">Collection</div>
                                <div class="spec-value"><?php echo htmlspecialchars($product['collection_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="desc-card">
                        <div class="desc-title">Details</div>
                        <div class="desc-text"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description provided.', ENT_QUOTES, 'UTF-8')); ?></div>
                    </div>

                    <?php if ((int)$product['stock'] > 0): ?>
                        <form method="POST" action="cart_action.php" class="action-row">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                            <input type="number" name="qty" class="qty-input" min="1" max="<?php echo (int)$product['stock']; ?>" value="1">
                            <button type="submit" class="primary-btn">Add to Cart</button>
                            <a href="shop.php" class="ghost-btn">Back to Shop</a>
                        </form>
                    <?php else: ?>
                        <div class="action-row">
                            <button class="primary-btn" type="button" disabled>Out of Stock</button>
                            <a href="shop.php" class="ghost-btn">Back to Shop</a>
                        </div>
                    <?php endif; ?>

                    <div class="trust-row">
                        <span>Free returns within 30 days</span>
                        <span>Secure checkout</span>
                        <span>Fast shipping</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
