<?php

class AuthLogoutController extends Controller
{
    public function index()
    {
        require_once project_path('includes/csrf.php');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate()) {
        	header('Location: ../public/csrf_error.php');
        	exit();
        }
        
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    }
}
