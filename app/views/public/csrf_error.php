

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Check Failed</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: var(--bg);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", sans-serif;
        }
        .csrf-shell {
            max-width: 640px;
            margin: 80px auto;
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
            text-align: center;
        }
        .csrf-shell h1 {
            font-size: 1.8rem;
            margin-bottom: 12px;
        }
        .csrf-shell p {
            color: var(--text-light);
            margin-bottom: 24px;
        }
        .csrf-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .csrf-actions a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="csrf-shell">
        <h1>Security Check Failed</h1>
        <p>Your session token is missing or expired. Please reload the page and try again.</p>
        <div class="csrf-actions">
            <a href="<?php echo htmlspecialchars($backUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline">Go Back</a>
            <a href="<?php echo htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary">Continue</a>
        </div>
    </div>
</body>
</html>
