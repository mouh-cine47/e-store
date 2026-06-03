<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking — E-Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <script src="../assets/js/dark-mode.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="navbar__container">
            <a href="home.php" class="navbar__brand">
                <i class="fas fa-shopping-bag"></i> E-Store
            </a>
            <div class="navbar__nav">
                <a href="home.php" class="navbar__link">Home</a>
                <a href="shop.php" class="navbar__link">Shop</a>
                <a href="orders.php" class="navbar__link active">Order Tracking</a>
            </div>
            <div class="navbar__actions">
                <button onclick="toggleDarkMode()" class="btn btn-outline btn-sm" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="cart.php" class="navbar__icon-btn" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="navbar__cart-badge"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                </a>
                <?php if (!empty($_SESSION['user_name'])): ?>
                    <div class="navbar__user">
                        <span><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <a href="../auth/logout.php" class="btn btn-outline btn-sm">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-primary btn-sm">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container py-6 space-y-6">
        <header class="space-y-2">
            <p class="text-sm uppercase tracking-widest text-muted">Track your purchase</p>
            <h1 class="text-3xl font-semibold">Order Tracking</h1>
            <p class="text-muted">Enter an order ID or review your most recent orders below.</p>
        </header>

        <?php if (!$hasOrders): ?>
            <div class="alert alert-warning">
                <h3 class="alert-title">No order history available</h3>
                <p>The orders system is not set up yet. Please place an order or contact support.</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <aside class="lg:col-span-4">
                <div class="card card-elevated">
                    <div class="card-body space-y-4">
                        <h2 class="text-xl font-semibold">Find your order</h2>
                        <form method="GET" class="space-y-4">
                            <label class="form-label" for="order_id">Order ID</label>
                            <input id="order_id" name="order_id" type="number" value="<?php echo htmlspecialchars($orderId ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="form-input">
                            <button type="submit" class="btn btn-primary w-full">View Order</button>
                        </form>
                    </div>
                </div>
            </aside>

            <section class="lg:col-span-8 space-y-6">
                <?php if (!empty($order)): ?>
                    <div class="card card-elevated">
                        <div class="card-body space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                                <div>
                                    <p class="text-sm uppercase tracking-widest text-muted mb-2">Order #<?php echo (int)$order['id']; ?></p>
                                    <h2 class="text-2xl font-semibold">Order Status</h2>
                                </div>
                                <span class="badge <?php echo ($order['status'] === 'delivered') ? 'badge-success' : (($order['status'] === 'shipped') ? 'badge-primary' : 'badge-warning'); ?>">
                                    <?php echo ucfirst(htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8')); ?>
                                </span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-muted">
                                <div>
                                    <p class="text-sm">Date</p>
                                    <p><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm">Total</p>
                                    <p>$<?php echo number_format((float)$order['total'], 2); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm">Shipping</p>
                                    <p><?php echo htmlspecialchars($order['shipping_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-elevated">
                        <div class="card-body space-y-4">
                            <h3 class="text-xl font-semibold">Shipment timeline</h3>
                            <div class="space-y-4">
                                <?php foreach ($statusHistory as $history): ?>
                                    <div class="card card-flat">
                                        <div class="card-body">
                                            <p class="text-sm text-muted"><?php echo date('F j, Y H:i', strtotime($history['created_at'])); ?></p>
                                            <p class="font-medium"><?php echo ucfirst(htmlspecialchars($history['status'], ENT_QUOTES, 'UTF-8')); ?></p>
                                            <?php if (!empty($history['message'])): ?>
                                                <p class="text-muted"><?php echo htmlspecialchars($history['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card card-elevated">
                        <div class="card-body">
                            <h3 class="text-xl font-semibold mb-4">Order Items</h3>
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Price</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="flex items-center gap-3">
                                                        <?php if (!empty($item['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-12 h-12 rounded-lg object-cover">
                                                        <?php endif; ?>
                                                        <span><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-right"><?php echo (int)$item['quantity']; ?></td>
                                                <td class="text-right">$<?php echo number_format((float)$item['price'], 2); ?></td>
                                                <td class="text-right">$<?php echo number_format((float)$item['price'] * (int)$item['quantity'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <?php if (!empty($allOrders)): ?>
            <section class="card card-elevated">
                <div class="card-body">
                    <h2 class="text-xl font-semibold mb-4">Recent Orders</h2>
                    <div class="grid gap-4">
                        <?php foreach ($allOrders as $recent): ?>
                            <a href="?order_id=<?php echo (int)$recent['id']; ?>" class="card card-flat hover:shadow-sm transition-shadow duration-200 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-medium">Order #<?php echo (int)$recent['id']; ?></p>
                                        <p class="text-sm text-muted"><?php echo date('F j, Y', strtotime($recent['created_at'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold">$<?php echo number_format((float)$recent['total'], 2); ?></p>
                                        <p class="text-sm text-muted"><?php echo ucfirst(htmlspecialchars($recent['status'], ENT_QUOTES, 'UTF-8')); ?></p>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
