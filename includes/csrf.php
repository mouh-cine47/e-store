<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    $token = csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $requestToken = $_POST['csrf_token'] ?? '';

    if ($sessionToken === '' || $requestToken === '') {
        return false;
    }

    return hash_equals($sessionToken, $requestToken);
}
