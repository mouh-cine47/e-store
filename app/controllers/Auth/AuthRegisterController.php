<?php

class AuthRegisterController extends Controller
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
                header('Location: ../public/home.php');
            }
            exit();
        }
        
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_validate()) {
                $error = 'Invalid form token. Please refresh and try again.';
            } else {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
        
            if ($name === '' || strlen($name) < 2) {
                $error = 'Please enter your full name.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    $error = 'An account already exists with this email.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $insert = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
                        $insert->execute([
                            'name' => $name,
                            'email' => $email,
                            'password' => $hash,
                            'role' => 'client',
                        ]);
                        $success = 'Account created. You can now log in.';
                    } catch (PDOException $exception) {
                        $error = 'Database schema is not updated. Please import database.sql.';
                    }
                }
            }
            }
        }
        $this->render('auth/register', get_defined_vars());
    }
}
