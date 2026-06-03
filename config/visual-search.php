<?php
/**
 * Configuration Visual Search - Clarifai API
 * 
 * Ce fichier configure l'API Clarifai pour la reconnaissance d'images
 * 
 * Utilise les variables d'environnement du fichier .env
 */

// Récupérer la clé API depuis .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    $clarifaiKey = $env['CLARIFAI_API_KEY'] ?? null;
    $clarifaiUrl = $env['CLARIFAI_API_URL'] ?? 'https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs';
} else {
    $clarifaiKey = getenv('CLARIFAI_API_KEY');
    $clarifaiUrl = getenv('CLARIFAI_API_URL') ?: 'https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs';
}

// Configuration Visual Search
return [
    // ===== CLARIFAI API =====
    'clarifai' => [
        'api_key' => $clarifaiKey,
        'api_url' => $clarifaiUrl,
        // ID du modèle Clarifai (General model pour reconnaissance d'objets)
        'model_id' => 'aaa03c23b3724a16a56b629203edc62c',
    ],
    
    // ===== UPLOAD IMAGE =====
    'upload' => [
        // Dossier de stockage temporaire des images
        'tmp_dir' => __DIR__ . '/../tmp/visual-search/',
        // Dossier public pour affichage
        'public_dir' => __DIR__ . '/../public/uploads/visual-search/',
        // Extensions autorisées
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
        // Taille max en bytes (5MB)
        'max_size' => 5 * 1024 * 1024,
        // Mime types autorisés
        'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
    ],
    
    // ===== RECHERCHE PRODUITS =====
    'search' => [
        // Nombre de produits à retourner
        'max_results' => 12,
        // Score de confiance minimum pour les tags (0-1)
        'min_confidence' => 0.5,
        // Colonnes à chercher dans la DB
        'search_fields' => ['name', 'description', 'category'],
    ],
    
    // ===== VALIDATION =====
    'validation' => [
        // Activer la validation des images
        'enabled' => true,
        // Résolution minimale
        'min_width' => 100,
        'min_height' => 100,
        // Résolution maximale
        'max_width' => 4000,
        'max_height' => 4000,
    ],
];
