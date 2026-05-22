<?php
/**
 * API Image Search - Version Simple (SANS Clarifai)
 * Utilise l'analyse d'image native PHP
 * 
 * À utiliser si : Clarifai ne fonctionne pas ou si vous préférez une solution sans API
 */

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config/pdo.php';
require_once __DIR__ . '/../app/models/ImageSearchSimple.php';

// Headers JSON
header('Content-Type: application/json');

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier qu'un fichier a été uploadé
if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Aucune image fournie']);
    exit;
}

try {
    // Initialiser la classe ImageSearchSimple
    $imageSearch = new ImageSearchSimple($pdo);

    // 1. Valider l'image
    $validation = $imageSearch->validateImageFile($_FILES['image']);
    if (!$validation['valid']) {
        http_response_code(400);
        echo json_encode(['error' => $validation['error']]);
        exit;
    }

    // 2. Sauvegarder l'image temporairement
    $tempImagePath = $imageSearch->saveUploadedImage($_FILES['image']);
    if (!$tempImagePath) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors du chargement du fichier']);
        exit;
    }

    // 3. Analyser l'image (extraction de couleurs + dimensions)
    $tags = $imageSearch->analyzeImageSimple($tempImagePath);
    
    if (empty($tags)) {
        // Nettoyer le fichier temporaire
        $imageSearch->cleanupTempFile($tempImagePath);
        
        http_response_code(400);
        echo json_encode(['error' => 'Impossible d\'analyser l\'image. Veuillez essayer une autre image.']);
        exit;
    }

    // 4. Rechercher les produits similaires
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    $products = $imageSearch->searchSimilarProducts($tags, $limit);

    // 5. Nettoyer le fichier temporaire
    $imageSearch->cleanupTempFile($tempImagePath);

    // 6. Retourner les résultats
    echo json_encode([
        'success' => true,
        'tags' => $tags,
        'count' => count($products),
        'products' => $products,
        'method' => 'image_analysis_simple'
    ]);

} catch (Exception $e) {
    error_log("Image Search Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>
