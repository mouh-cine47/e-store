<?php
/**
 * API Visual Search - Endpoint AJAX
 * 
 * Reçoit l'image uploadée et retourne les résultats en JSON
 */

// === CONFIGURATION ===
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Créer réponse d'erreur par défaut
function errorResponse($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
    ]);
    exit;
}

// === VÉRIFIER LA MÉTHODE =================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Méthode non autorisée. Utiliser POST.', 405);
}

// === CHARGER LA CONFIGURATION ===
$configFile = __DIR__ . '/../../config/visual-search.php';
if (!file_exists($configFile)) {
    errorResponse('Fichier de configuration manquant', 500);
}

$config = require $configFile;

// === CHARGER LES CLASSES ===
$imageUploadFile = __DIR__ . '/../../app/core/ImageUpload.php';
$visualSearchFile = __DIR__ . '/../../app/core/VisualSearch.php';

if (!file_exists($imageUploadFile)) {
    errorResponse('Classe ImageUpload manquante', 500);
}
if (!file_exists($visualSearchFile)) {
    errorResponse('Classe VisualSearch manquante', 500);
}

require_once $imageUploadFile;
require_once $visualSearchFile;

// === CHARGER LA DATABASE ===
$pdoFile = __DIR__ . '/../../config/pdo.php';
if (!file_exists($pdoFile)) {
    errorResponse('Connexion database manquante', 500);
}

require_once $pdoFile;

try {
    $pdo = Database::connection();
} catch (Exception $e) {
    errorResponse('Erreur connexion database: ' . $e->getMessage(), 500);
}

// === CRÉER INSTANCES ===
$imageUpload = new ImageUpload($config);
$visualSearch = new VisualSearch($pdo, $config);

// === ÉTAPE 1: UPLOADER L'IMAGE ===
if (!isset($_FILES['image'])) {
    errorResponse('Aucune image fournie', 400);
}

$imagePath = $imageUpload->upload($_FILES['image']);

if ($imagePath === false) {
    errorResponse($imageUpload->getError(), 400);
}

// === ÉTAPE 2: ANALYSER L'IMAGE & CHERCHER PRODUITS ===
$result = $visualSearch->search($imagePath);

// === NETTOYER LE FICHIER TEMPORAIRE ===
@unlink($imagePath);

// === RETOURNER LA RÉPONSE JSON ===
if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    errorResponse($result['error'], 500);
}
