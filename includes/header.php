<?php
$requiredRole = 'admin';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/csrf.php';
$pdo = Database::connection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Store - Admin</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap + Unified Design System -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <!-- Dark Mode Toggle -->
    <script src="../assets/js/dark-mode.js"></script>
</head>
<body class="admin-body">
    <div id="wrapper">
