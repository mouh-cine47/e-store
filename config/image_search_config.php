<?php
/**
 * Configuration de la recherche par image
 * Permet de basculer facilement entre Clarifai et analyse simple
 */

// ========================================
// CHOISIR LA MÉTHODE DE RECHERCHE
// ========================================

// Option 1: Utiliser Clarifai AI (recommandé)
// Plus précis, mais nécessite une clé API
// Copiez-collez votre token Clarifai ci-dessous
define('USE_CLARIFAI', false);  // ← Mettre à true si vous avez une clé Clarifai
define('CLARIFAI_API_KEY', 'VOTRE_CLE_CLARIFAI_ICI');

// Option 2: Utiliser l'analyse d'image simple (PHP natif)
// Ne nécessite pas d'API externe
// Analyse les couleurs et dimensions de l'image
define('USE_SIMPLE_ANALYSIS', true);  // ← Mettre à false pour utiliser Clarifai

// ========================================
// CONFIGURATION COMMUNE
// ========================================

// Nombre de produits à retourner
define('MAX_PRODUCTS', 12);

// Taille max de l'image (en bytes) = 5MB
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Extensions acceptées
define('ALLOWED_EXTENSIONS', ['jpeg', 'jpg', 'png', 'webp']);

// Dossier temporaire pour les uploads
define('UPLOAD_DIR', __DIR__ . '/../../tmp/uploads/');

// ========================================
// CONFIGURATION CLARIFAI
// ========================================

// URL de l'API Clarifai (ne pas changer)
define('CLARIFAI_API_URL', 'https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs');

// Score minimum de confiance pour un tag (0 à 1)
// 0.5 = 50% de confiance minimum
define('CLARIFAI_MIN_CONFIDENCE', 0.5);

// Timeout pour l'API Clarifai (en secondes)
define('CLARIFAI_TIMEOUT', 30);

// ========================================
// CONFIGURATION ANALYSE SIMPLE
// ========================================

// Couleurs à extraire : nombre de couleurs dominantes
define('COLOR_EXTRACTION_LIMIT', 3);

// Taille du thumbnail pour l'analyse rapide (en pixels)
define('ANALYSIS_THUMBNAIL_SIZE', 10);

// ========================================
// FONCTION HELPER
// ========================================

/**
 * Retourne la classe et l'endpoint API à utiliser
 */
function getImageSearchConfig() {
    if (USE_CLARIFAI) {
        return [
            'method' => 'clarifai',
            'class' => 'ImageSearch',
            'endpoint' => './api_image_search.php'
        ];
    } else {
        return [
            'method' => 'simple',
            'class' => 'ImageSearchSimple',
            'endpoint' => './api_image_search_simple.php'
        ];
    }
}

/**
 * Valider la configuration
 */
function validateImageSearchConfig() {
    $errors = [];

    // Vérifier que USE_CLARIFAI et USE_SIMPLE_ANALYSIS ne sont pas tous les deux true
    if (USE_CLARIFAI && USE_SIMPLE_ANALYSIS) {
        $errors[] = "⚠️ Les deux méthodes ne peuvent pas être activées simultanément. Mettez une à false.";
    }

    // Si Clarifai est activé, vérifier la clé API
    if (USE_CLARIFAI && CLARIFAI_API_KEY === 'VOTRE_CLE_CLARIFAI_ICI') {
        $errors[] = "⚠️ Clarifai est activé mais la clé API n'est pas configurée.";
    }

    // Vérifier que le dossier upload existe
    if (!is_dir(UPLOAD_DIR)) {
        $success = @mkdir(UPLOAD_DIR, 0755, true);
        if (!$success) {
            $errors[] = "❌ Impossible de créer le dossier d'upload: " . UPLOAD_DIR;
        }
    }

    return $errors;
}

// ========================================
// DIAGNOSTIC
// ========================================

/**
 * Afficher le diagnostic du système (pour debug)
 */
function getImageSearchDiagnostics() {
    $config = getImageSearchConfig();
    $errors = validateImageSearchConfig();

    $diagnostics = [
        'method' => $config['method'],
        'class' => $config['class'],
        'endpoint' => $config['endpoint'],
        'errors' => $errors,
        'php_gd_enabled' => extension_loaded('gd'),
        'php_curl_enabled' => extension_loaded('curl'),
        'upload_dir_exists' => is_dir(UPLOAD_DIR),
        'upload_dir_writable' => is_writable(UPLOAD_DIR),
        'max_file_size' => (MAX_FILE_SIZE / 1024 / 1024) . 'MB'
    ];

    if (USE_CLARIFAI) {
        $diagnostics['clarifai_api_key_set'] = (CLARIFAI_API_KEY !== 'VOTRE_CLE_CLARIFAI_ICI');
        $diagnostics['clarifai_url'] = CLARIFAI_API_URL;
    }

    return $diagnostics;
}

?>
