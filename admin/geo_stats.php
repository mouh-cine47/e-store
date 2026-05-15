<?php
include '../includes/header.php';
include '../includes/sidebar.php';
$pdo = Database::connection();

$tableStmt = $pdo->query("SHOW TABLES LIKE 'product_views'");
$hasProductViews = (bool)$tableStmt->fetch();

$totalVisits = 0;
$countryStats = [];
$cityStats = [];
$productStats = [];

if ($hasProductViews) {
    $totalVisitsStmt = $pdo->query('SELECT COUNT(*) AS total FROM product_views');
    $totalVisits = (int)($totalVisitsStmt->fetch()['total'] ?? 0);

    $countryStmt = $pdo->query(
        'SELECT country, COUNT(*) AS visits '
        . 'FROM product_views '
        . 'GROUP BY country '
        . 'ORDER BY visits DESC '
        . 'LIMIT 10'
    );
    $countryStats = $countryStmt->fetchAll();

    $cityStmt = $pdo->query(
        'SELECT city, country, COUNT(*) AS visits '
        . 'FROM product_views '
        . 'GROUP BY city, country '
        . 'ORDER BY visits DESC '
        . 'LIMIT 10'
    );
    $cityStats = $cityStmt->fetchAll();

    $productStmt = $pdo->query(
        'SELECT p.name, COUNT(v.id) AS views '
        . 'FROM product_views v '
        . 'JOIN products p ON p.id = v.product_id '
        . 'GROUP BY v.product_id '
        . 'ORDER BY views DESC '
        . 'LIMIT 10'
    );
    $productStats = $productStmt->fetchAll();
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Geolocation Stats</h1>
    </div>

    <?php if (!$hasProductViews): ?>
        <div class="alert alert-warning">
            The geolocation table is missing. Import database_ecommerce.sql to create product_views.
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card stats-card primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Visits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalVisits; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marker-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Countries</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($countryStats as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['country'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int)$row['visits']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($countryStats) === 0): ?>
                            <div class="alert alert-info">No visits recorded yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Cities</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>City</th>
                                    <th>Country</th>
                                    <th>Visits</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cityStats as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['city'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['country'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int)$row['visits']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($cityStats) === 0): ?>
                            <div class="alert alert-info">No visits recorded yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Most Viewed Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productStats as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo (int)$row['views']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($productStats) === 0): ?>
                            <div class="alert alert-info">No product views recorded yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
