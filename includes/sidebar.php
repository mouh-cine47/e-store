<!-- Sidebar -->
<nav id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-store me-2"></i>E-Store</h3>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <a href="../admin/users.php"><i class="fas fa-user-friends me-2"></i> Clients</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
            <a href="../admin/orders.php"><i class="fas fa-receipt me-2"></i> Orders</a>
        </li>
        <li class="<?php echo strpos($_SERVER['PHP_SELF'], '/products/') !== false ? 'active' : ''; ?>">
            <a href="../products/index.php"><i class="fas fa-box me-2"></i> Products</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
            <a href="../admin/categories.php"><i class="fas fa-tags me-2"></i> Categories</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'classification.php' ? 'active' : ''; ?>">
            <a href="../admin/classification.php"><i class="fas fa-brain me-2"></i> AI Classification</a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'geo_stats.php' ? 'active' : ''; ?>">
            <a href="../admin/geo_stats.php"><i class="fas fa-map-marker-alt me-2"></i> Geo Stats</a>
        </li>
        <hr class="mx-3 bg-white opacity-25">
        <li>
            <a href="../auth/logout.php" class="text-warning"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </li>
    </ul>
</nav>

<div id="content">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1 text-primary">E-Store Admin</span>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 text-secondary">Welcome, <strong><?php echo $_SESSION['user_name']; ?></strong></span>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                </div>
            </div>
        </div>
    </nav>
