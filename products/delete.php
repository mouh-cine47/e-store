<?php
require_once '../includes/auth_check.php';
require_once __DIR__ . '/../app/bootstrap.php';

$pdo = Database::connection();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');

    if ($stmt->execute(['id' => $id])) {
        header('Location: index.php?msg=deleted');
    } else {
        header('Location: index.php?error=failed');
    }
}
exit();
?>
