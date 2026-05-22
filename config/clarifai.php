<?php
/**
 * Configuration Clarifai API
 * API gratuit pour l'analyse d'images
 * Inscrivez-vous sur https://clarifai.com (gratuit, pas de carte bancaire nécessaire)
 */

// Clé API Clarifai (vous devez la générer sur le site)
define('CLARIFAI_API_KEY', 'REMPLACER_PAR_VOTRE_CLE_API');

// URL de l'API Clarifai (ne pas changer)
define('CLARIFAI_API_URL', 'https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs');

// Temps d'expiration du cache des résultats (en secondes)
define('CACHE_EXPIRY', 3600);
?>
