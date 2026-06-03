

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - E-Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <script src="../assets/js/dark-mode.js"></script>
    <style>
        html, body {
            min-height: 100%;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-4);
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-card {
            background: var(--bg-primary);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-8);
        }

        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }

        .auth-logo {
            font-size: var(--text-5xl);
            color: var(--color-primary);
            margin-bottom: var(--spacing-3);
        }

        .auth-title {
            font-size: var(--text-3xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            margin-bottom: var(--spacing-2);
        }

        .auth-subtitle {
            color: var(--text-secondary);
            font-size: var(--text-base);
        }

        .form-group {
            margin-bottom: var(--spacing-6);
        }

        .form-label {
            display: block;
            margin-bottom: var(--spacing-2);
            font-weight: var(--font-semibold);
            color: var(--text-primary);
            font-size: var(--text-sm);
        }

        .form-icon {
            margin-right: var(--spacing-2);
            color: var(--color-primary);
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-2) var(--spacing-3);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            font-size: var(--text-base);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: all var(--transition-base);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(163, 136, 96, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: var(--spacing-3);
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: var(--font-semibold);
            font-size: var(--text-base);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .btn-register:hover {
            background: var(--color-primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .auth-footer {
            text-align: center;
            margin-top: var(--spacing-6);
        }

        .auth-footer a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: var(--font-semibold);
            transition: color var(--transition-fast);
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
        .alert-danger {
            background: var(--color-danger-light);
            border: 1px solid var(--color-danger);
            color: var(--color-danger);
            border-radius: var(--radius-md);
            padding: var(--spacing-3) var(--spacing-4);
            margin-bottom: var(--spacing-6);
            font-size: var(--text-sm);
        }
        .alert-success {
            background: var(--color-success-light);
            border: 1px solid var(--color-success);
            color: var(--color-success);
            border-radius: var(--radius-md);
            padding: var(--spacing-3) var(--spacing-4);
            margin-bottom: var(--spacing-6);
            font-size: var(--text-sm);
        }
        .form-icon {
            color: var(--color-primary);
            margin-right: var(--spacing-2);
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
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label" for="name">
                        <i class="fas fa-user form-icon"></i>Full Name
                    </label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">
                        <i class="fas fa-envelope form-icon"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock form-icon"></i>Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">
                        <i class="fas fa-lock form-icon"></i>Confirm Password
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p class="text-secondary" style="font-size: var(--text-sm);">
                    Already have an account? <a href="login.php">Sign in here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
