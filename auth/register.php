<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../includes/csrf.php';
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        }
        .auth-container {
            width: 100%;
            max-width: 420px;
        }
        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .auth-logo {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 12px;
        }
        .auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
        }
        .auth-subtitle {
            color: var(--text-light);
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text);
            font-size: 0.95rem;
        }
        .form-control {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            transition: var(--transition);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
            outline: none;
        }
        .btn-register {
            background: var(--primary);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }
        .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .auth-footer {
            text-align: center;
            margin-top: 24px;
        }
        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        .auth-footer a:hover {
            color: var(--primary-dark);
        }
        .alert-danger {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        .alert-success {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #047857;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        .form-icon {
            color: var(--text-light);
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join E-Store today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user form-icon"></i>Full Name
                    </label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope form-icon"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock form-icon"></i>Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock form-icon"></i>Confirm Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p style="color: var(--text-light); font-size: 0.9rem;">
                    Already have an account? <a href="login.php">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
