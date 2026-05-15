<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
$pdo = Database::connection();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$total = 0.0;
foreach ($cart as $item) {
    $total += ((float)$item['price']) * (int)$item['qty'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="shop.php">E-Store</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-secondary">Hi, <?php echo htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="orders.php" class="btn btn-outline-primary btn-sm me-2">My Orders</a>
                <a href="../auth/logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0">Your Cart</h4>
            <a href="shop.php" class="btn btn-outline-secondary btn-sm">Continue Shopping</a>
        </div>

        <?php if (count($cart) === 0): ?>
            <div class="alert alert-info">Your cart is empty.</div>
        <?php else: ?>
            <div class="table-responsive mb-3">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>
                                <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                                <td style="max-width: 140px;">
                                    <input type="number" name="qty[<?php echo (int)$item['id']; ?>]" class="form-control" min="1" max="<?php echo (int)$item['stock']; ?>" value="<?php echo (int)$item['qty']; ?>" form="cart-update">
                                </td>
                                <td>$<?php echo number_format((float)$item['price'] * (int)$item['qty'], 2); ?></td>
                                <td>
                                    <form method="POST" action="cart_action.php" class="d-inline">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form method="POST" action="cart_action.php" id="cart-update">
                <input type="hidden" name="action" value="update">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <button type="submit" class="btn btn-outline-primary">Update Cart</button>
                        <button type="submit" name="action" value="clear" class="btn btn-outline-danger" formaction="cart_action.php">Clear Cart</button>
                    </div>
                    <div class="text-end">
                        <div class="fs-5">Total: <strong>$<?php echo number_format($total, 2); ?></strong></div>
                        <a href="checkout.php" class="btn btn-primary mt-2">Proceed to Checkout</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>
