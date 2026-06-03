<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - E-Store</title>
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
                </a>
                <div class="navbar__user">
                    <span><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <form method="POST" action="../auth/logout.php" class="navbar__logout-form">
                        <?php csrf_field(); ?>
                        <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="navbar__bottom">
            <a href="home.php" class="navbar__link">Home</a>
            <a href="shop.php" class="navbar__link">Shop</a>
            <a href="orders.php" class="navbar__link navbar__link--active">My Orders</a>
        </div>
    </nav>

    <main class="container py-6 space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-semibold">My Orders</h1>
                <p class="text-muted">Review your purchase history and order details.</p>
            </div>
            <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
        </div>

        <?php if (!isset($hasOrders) || !$hasOrders): ?>
            <div class="alert alert-warning">
                <h3 class="alert-title">Orders unavailable</h3>
                <p>The orders table is missing. Import the database or connect the order history table.</p>
            </div>
        <?php endif; ?>

        <?php if (($hasOrders ?? false) && !($hasOrderItems ?? false)): ?>
            <div class="alert alert-warning">
                <h3 class="alert-title">Incomplete order data</h3>
                <p>The order items table is missing. Import database.sql to see full order details.</p>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <h3 class="alert-title">No orders yet</h3>
                <p>You have not placed any orders yet. Start shopping to create your first order.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                    <section class="card card-elevated">
                        <div class="card-body space-y-4">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <p class="text-sm uppercase tracking-widest text-muted mb-2">Order #<?php echo (int)$order['id']; ?></p>
                                    <h2 class="text-xl font-semibold">$<?php echo number_format((float)$order['total'], 2); ?></h2>
                                </div>
                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="badge badge-soft"><?php echo htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="text-sm text-muted"><?php echo htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <?php foreach ($itemsByOrder[$order['id']] ?? [] as $item): ?>
                                    <div class="card card-flat p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-sm">
                                                <span class="font-medium"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <span class="text-muted">x<?php echo (int)$item['quantity']; ?></span>
                                            </div>
                                            <span>$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
