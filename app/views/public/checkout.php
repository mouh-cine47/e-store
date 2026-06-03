<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-Store</title>
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
                <a href="checkout.php" class="navbar__link active">Checkout</a>
            </div>
            <div class="navbar__actions">
                <button onclick="toggleDarkMode()" class="btn btn-outline btn-sm" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="cart.php" class="navbar__icon-btn" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                </a>
                <div class="navbar__user">
                    <span><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <form method="POST" action="../auth/logout.php" class="inline-form">
                        <?php csrf_field(); ?>
                        <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-6">
        <header class="mb-6">
            <h1 class="text-3xl font-semibold">Checkout</h1>
            <p class="text-muted">Complete your order with secure delivery and payment details.</p>
        </header>

        <?php if (!$canCheckout): ?>
            <div class="alert alert-warning mb-6">
                <h3 class="alert-title">Checkout unavailable</h3>
                <p>Required tables are missing. Import database.sql to place orders.</p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger mb-6"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success mb-6">
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <div class="mt-4 flex flex-wrap gap-3">
                    <a href="orders.php" class="btn btn-primary">View Orders</a>
                    <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-7 gap-8">
            <section class="lg:col-span-4 card card-elevated">
                <div class="card-body space-y-4">
                    <h2 class="text-xl font-semibold">Shipping Information</h2>
                    <form method="POST" class="space-y-4">
                        <?php csrf_field(); ?>
                        <div>
                            <label class="form-label" for="shipping_name">Full Name</label>
                            <input id="shipping_name" name="shipping_name" type="text" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="shipping_phone">Phone</label>
                            <input id="shipping_phone" name="shipping_phone" type="text" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label" for="shipping_address">Address</label>
                            <input id="shipping_address" name="shipping_address" type="text" class="form-input" required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label" for="city">City</label>
                                <input id="city" name="city" type="text" class="form-input" required>
                            </div>
                            <div>
                                <label class="form-label" for="country">Country</label>
                                <input id="country" name="country" type="text" class="form-input" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" <?php echo $canCheckout ? '' : 'disabled'; ?>>Place Order</button>
                    </form>
                </div>
            </section>

            <section class="lg:col-span-3 card card-elevated">
                <div class="card-body space-y-4">
                    <h2 class="text-xl font-semibold">Order Summary</h2>
                    <?php if (empty($cart)): ?>
                        <p class="text-muted">Your cart is empty.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($cart as $item): ?>
                                <div class="card card-flat p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="text-sm text-muted">x<?php echo (int)$item['qty']; ?></p>
                                        </div>
                                        <span>$<?php echo number_format((float)$item['price'] * (int)$item['qty'], 2); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
