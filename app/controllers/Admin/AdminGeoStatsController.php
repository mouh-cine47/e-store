<?php

class AdminGeoStatsController extends Controller
{
    public function index()
    {
        include project_path('includes/header.php');
        include project_path('includes/sidebar.php');
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
        $this->render('admin/geo_stats', get_defined_vars());
    }
}
