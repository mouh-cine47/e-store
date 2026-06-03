<?php

class PageTracker
{
    public static function track(PDO $pdo, $pageKey, $pageTitle)
    {
        $tableStmt = $pdo->query("SHOW TABLES LIKE 'page_views'");
        if (!$tableStmt->fetch()) {
            return;
        }

        $ip = Geo::getClientIp();
        $geo = Geo::lookup($ip);

        $stmt = $pdo->prepare(
            'INSERT INTO page_views (page_key, page_title, user_id, ip, city, country) '
            . 'VALUES (:page_key, :page_title, :user_id, :ip, :city, :country)'
        );
        $stmt->execute([
            'page_key' => $pageKey,
            'page_title' => $pageTitle,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip' => $ip,
            'city' => $geo['city'],
            'country' => $geo['country'],
        ]);
    }
}
