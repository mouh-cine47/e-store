

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="shop.php">E-Store</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-secondary">Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="cart.php" class="btn btn-outline-primary btn-sm me-2">Cart</a>
                <form method="POST" action="../auth/logout.php" class="d-inline">
                    <?php csrf_field(); ?>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="mb-3">Checkout</h4>

        <?php if (!$canCheckout): ?>
            <div class="alert alert-warning">Required tables are missing. Import database.sql to place orders.</div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                <div class="mt-2">
                    <a href="orders.php" class="btn btn-sm btn-success">View Orders</a>
                    <a href="shop.php" class="btn btn-sm btn-outline-secondary">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="POST">
                            <?php csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="shipping_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="shipping_phone" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="shipping_address" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" <?php echo $canCheckout ? '' : 'disabled'; ?>>Place Order</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($cart as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        <small class="text-muted">x<?php echo (int)$item['qty']; ?></small>
                                    </div>
                                    <span>$<?php echo number_format((float)$item['price'] * (int)$item['qty'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
