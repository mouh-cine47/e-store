<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - E-Store</title>
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
            </div>
            <div class="navbar__actions">
                <button onclick="toggleDarkMode()" class="btn btn-outline btn-sm" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="orders.php" class="navbar__icon-btn" title="Orders">
                    <i class="fas fa-list"></i>
                </a>
                <div class="navbar__user inline-flex items-center gap-2">
                    <span><?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <form method="POST" action="../auth/logout.php" class="inline-flex items-center">
                        <?php csrf_field(); ?>
                        <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-6 space-y-6">
        <header class="space-y-2">
            <p class="text-sm uppercase tracking-widest text-muted">Shopping Cart</p>
            <h1 class="text-3xl font-semibold">Your Cart</h1>
            <p class="text-muted">Review your items, update quantities, or continue browsing.</p>
        </header>

        <?php if (count($cart) === 0): ?>
            <div class="alert alert-info">
                <h3 class="alert-title">Cart empty</h3>
                <p>Your shopping cart is currently empty. Start browsing our collection to add something new.</p>
                <a href="shop.php" class="btn btn-primary mt-3">Browse Shop</a>
            </div>
        <?php else: ?>
            <div class="flex flex-col gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-semibold">Cart Items</h2>
                        <p class="text-muted">Update quantities, remove items, or proceed to checkout.</p>
                    </div>
                    <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
                </div>

                <div class="card card-elevated overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-right">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $item): ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>" class="w-16 h-16 rounded-lg object-cover">
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-semibold"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">$<?php echo number_format((float)$item['price'], 2); ?></td>
                                    <td class="text-center">
                                        <input type="number" name="qty[<?php echo (int)$item['id']; ?>]" class="form-input text-center" min="1" max="<?php echo (int)$item['stock']; ?>" value="<?php echo (int)$item['qty']; ?>" form="cart-update">
                                    </td>
                                    <td class="text-right">$<?php echo number_format((float)$item['price'] * (int)$item['qty'], 2); ?></td>
                                    <td class="text-right">
                                        <form method="POST" action="cart_action.php" class="inline-flex">
                                            <?php csrf_field(); ?>
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                                            <button type="submit" class="btn btn-outline">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="cart_action.php" id="cart-update" class="grid gap-4 lg:grid-cols-[1.3fr_0.7fr]">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="update">

                    <div class="flex flex-wrap gap-3 items-center">
                        <button type="submit" class="btn btn-primary">Update Cart</button>
                        <button type="submit" name="action" value="clear" class="btn btn-outline">Clear Cart</button>
                    </div>

                    <div class="card card-elevated p-5">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-muted">Subtotal</span>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </div>
                            <p class="text-sm text-muted">Shipping and taxes calculated at checkout.</p>
                            <a href="checkout.php" class="btn btn-primary w-full">Proceed to Checkout</a>
                        </div>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
