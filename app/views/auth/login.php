

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <script src="../assets/js/dark-mode.js"></script>
    <style>
        html, body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                linear-gradient(125deg, rgba(28, 31, 33, 0.88), rgba(90, 63, 95, 0.76)),
                linear-gradient(135deg, #dfe7e1 0%, #f4efe7 100%) !important;
        }

        .auth-container {
            width: 100%;
            max-width: 460px;
        }

        .auth-card {
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 28px 70px rgba(20, 24, 28, 0.24);
            padding: 42px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-logo {
            width: 50px;
            height: 50px;
            display: grid;
            place-items: center;
            margin: 0 auto 18px;
            margin-bottom: 18px;
            border-radius: 8px;
            background: var(--ui-sage-soft);
            color: var(--ui-sage);
            font-size: 1.35rem;
        }

        .auth-title {
            margin: 0 0 8px;
            color: var(--ui-ink);
            font-family: var(--ui-display);
            font-size: 2.15rem;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.1;
        }

        .auth-subtitle {
            color: var(--ui-muted);
            font-size: 0.96rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: var(--ui-ink);
            font-size: 0.95rem;
            font-weight: 800;
        }

        .form-icon {
            width: 20px;
            margin-right: 10px;
            color: var(--ui-plum);
            text-align: center;
        }

        .form-control {
            display: block;
            width: 100%;
            max-width: 100%;
            height: 64px;
            min-height: 64px;
            padding: 16px 18px;
            border: 2px solid #d8ded6;
            border-radius: 8px;
            background: #fff;
            color: #1c1f21;
            font-size: 1.08rem;
            font-weight: 600;
            line-height: 1.35;
            box-shadow: 0 8px 20px rgba(29, 35, 39, 0.04);
            transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
        }

        .form-control::placeholder {
            color: #9aa19d;
            font-weight: 500;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f7f6f;
            box-shadow:
                0 0 0 4px rgba(79, 127, 111, 0.14),
                0 12px 28px rgba(29, 35, 39, 0.08);
            transform: translateY(-1px);
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            height: 62px;
            min-height: 62px;
            margin-top: 6px;
            border: 0;
            border-radius: 8px;
            background: var(--ui-plum);
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
            box-shadow: 0 14px 26px rgba(90, 63, 95, 0.22);
            transition: background 160ms ease, transform 160ms ease;
        }

        .btn-login:hover {
            background: var(--ui-plum-dark);
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
        }

        .auth-footer a {
            color: var(--ui-plum);
            text-decoration: none;
            font-weight: 800;
        }

        .auth-footer a:hover {
            color: var(--ui-plum-dark);
        }

        .alert-danger {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid #f1b6bd;
            border-radius: 8px;
            background: #f8e4e6;
            color: var(--ui-danger);
            font-size: 0.92rem;
            font-weight: 700;
        }

        @media (max-width: 800px) {
            body {
                padding: 16px;
            }

            .auth-card {
                padding: 30px 22px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your E-Store account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php csrf_field(); ?>
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
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p class="text-secondary" style="font-size: var(--text-sm); margin-bottom: var(--spacing-3);">
                    Don't have an account? <a href="register.php">Sign up here</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
