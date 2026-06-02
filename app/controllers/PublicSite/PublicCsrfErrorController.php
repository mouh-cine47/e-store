<?php

class PublicCsrfErrorController extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $targetUrl = '../auth/login.php';
        if (!empty($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            $targetUrl = '../admin/dashboard.php';
        } elseif (!empty($_SESSION['user_id'])) {
            $targetUrl = 'home.php';
        }
        
        $backUrl = $_SERVER['HTTP_REFERER'] ?? $targetUrl;
        $this->render('public/csrf_error', get_defined_vars());
    }
}
