<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: public/home.php');
    }
} else {
    header('Location: auth/login.php');
}
exit();
?>
