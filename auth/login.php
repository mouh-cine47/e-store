<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../public/shop.php');
            }
            exit();
        }

        $error = 'Invalid email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }
        .btn-primary {
            background: #764ba2;
            border: none;
        }
        .btn-primary:hover {
            background: #5a3a8a;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center mb-4">E-Store Login</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
        </form>
        <div class="text-center mt-3">
            <a href="register.php">Create an account</a>
        </div>
    </div>

</body>
</html>
