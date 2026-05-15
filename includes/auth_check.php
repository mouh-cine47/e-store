<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (isset($requiredRole) && $_SESSION['user_role'] !== $requiredRole) {
    header('Location: ../public/shop.php');
    exit();
}
?>
