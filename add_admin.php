<?php
/**
 * Script pour ajouter un nouvel administrateur
 * À exécuter une seule fois via le navigateur
 * 
 * URL: http://localhost/projet_php/e-store/add_admin.php
 */

require_once __DIR__ . '/app/bootstrap.php';

$pdo = Database::connection();

// Données du nouvel admin
$name = 'Admin2';
$email = 'admin2@gmail.com';
$password = 'admin123';

// Générer le hash bcrypt
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Vérifier si l'email existe déjà
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->fetch()) {
        echo "❌ Erreur : Cet email existe déjà !";
        exit;
    }

    // Ajouter le nouvel admin
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, 'admin')
    ");
    
    $stmt->execute([$name, $email, $hashedPassword]);
    
    echo "✅ Admin créé avec succès ! <br><br>";
    echo "<strong>Email :</strong> $email <br>";
    echo "<strong>Password :</strong> $password <br><br>";
    echo "<a href='auth/login.php'>← Se connecter</a>";

} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}

?>
