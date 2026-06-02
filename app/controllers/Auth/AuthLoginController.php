<?php

class AuthLoginController extends Controller
{
    public function index()
    {
        session_start();
        // Bootstrap loaded by the route wrapper.
        require_once project_path('includes/csrf.php');
        $pdo = Database::connection();
        
        if (isset($_SESSION['user_id'])) {
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../public/shop.php');
            }
            exit();
        }
        
        $error = '';
        
        define('MAX_LOGIN_ATTEMPTS', 5);
        define('LOGIN_LOCKOUT_SECONDS', 15 * 60);
        
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [
                'count' => 0,
                'locked_until' => 0,
            ];
        }
        
        function login_is_locked()
        {
            return (int)($_SESSION['login_attempts']['locked_until'] ?? 0) > time();
        }
        
        function login_register_failure()
        {
            $attempts = $_SESSION['login_attempts'];
            $attempts['count'] = (int)($attempts['count'] ?? 0) + 1;
        
            if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
                $attempts['locked_until'] = time() + LOGIN_LOCKOUT_SECONDS;
                $attempts['count'] = 0;
            }
        
            $_SESSION['login_attempts'] = $attempts;
        }
        
        function login_reset_attempts()
        {
            $_SESSION['login_attempts'] = [
                'count' => 0,
                'locked_until' => 0,
            ];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $error = 'Invalid form token. Please refresh and try again.';
            } elseif (login_is_locked()) {
                $remaining = (int)($_SESSION['login_attempts']['locked_until'] - time());
                $minutes = (int)ceil($remaining / 60);
                $error = 'Too many login attempts. Try again in ' . $minutes . ' minute(s).';
            } else {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
        
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif ($password === '') {
                $error = 'Please enter your password.';
            } else {
                $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1');
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();
        
                if ($user && password_verify($password, $user['password'])) {
                    login_reset_attempts();
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
        
                    if ($user['role'] === 'admin') {
                        header('Location: ../admin/dashboard.php');
                    } else {
                        header('Location: ../public/home.php');
                    }
                    exit();
                }
        
                login_register_failure();
                $error = 'Invalid email or password.';
            }
            }
        }
        $this->render('auth/login', get_defined_vars());
    }
}
