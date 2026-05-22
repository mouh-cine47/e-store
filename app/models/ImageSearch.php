<?php
/**
 * Classe ImageSearch
 * Gère l'analyse d'images et la recherche de produits similaires
 */

class ImageSearch {
    private $db;
    private $apiKey;
    private $apiUrl;

    public function __construct($database, $apiKey, $apiUrl) {
        $this->db = $database;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    /**
     * Vérifie si le fichier est valide
     * @param array $file - Fichier $_FILES
     * @return array - ['valid' => bool, 'error' => string]
     */
    public function validateImageFile($file) {
        // Vérifier si le fichier existe
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Erreur lors du chargement du fichier.'];
        }

        // Vérifier la taille (max 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Le fichier est trop volumineux (max 5MB).'];
        }

        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Format d\'image non autorisé. Utilisez JPG, PNG ou WebP.'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Envoie l'image à Clarifai pour l'analyse
     * @param string $imagePath - Chemin du fichier image
     * @return array - Tags extraits de l'image
     */
    public function analyzeImageWithClarifai($imagePath) {
        // Lire l'image et la convertir en base64
        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);

        // Préparer la requête pour Clarifai
        $postData = json_encode([
            'inputs' => [
                [
                    'data' => [
                        'image' => [
                            'base64' => $base64Image
                        ]
                    ]
                ]
            ]
        ]);

        // Effectuer l'appel API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Key ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Vérifier la réponse
        if ($httpCode !== 200) {
            error_log("Clarifai API Error: " . $response);
            return [];
        }

        $result = json_decode($response, true);

        // Extraire les tags de la réponse
        $tags = [];
        if (isset($result['outputs'][0]['data']['concepts'])) {
            foreach ($result['outputs'][0]['data']['concepts'] as $concept) {
                // Garder seulement les tags avec une confiance > 0.5
                if ($concept['value'] > 0.5) {
                    $tags[] = strtolower($concept['name']);
                }
            }
        }

        return $tags;
    }

    /**
     * Recherche les produits similaires basés sur les tags
     * @param array $tags - Tags extraits de l'image
     * @param int $limit - Nombre de résultats
     * @return array - Produits trouvés
     */
    public function searchSimilarProducts($tags, $limit = 12) {
        if (empty($tags)) {
            return [];
        }

        // Construire la requête SQL
        // Chercher les produits contenant les tags dans name, description, brand, color, size
        $searchTerm = implode(' ', array_map(function($tag) {
            return '+' . addslashes($tag);
        }, $tags));

        $query = "SELECT * FROM products 
                  WHERE is_active = 1 
                  AND stock > 0
                  AND (
                      MATCH(name, description, brand) AGAINST(:search IN BOOLEAN MODE)
                      OR color LIKE :colorLike
                      OR size LIKE :sizeLike
                      OR collection_name LIKE :collectionLike
                  )
                  ORDER BY 
                    CASE 
                      WHEN MATCH(name, description, brand) AGAINST(:search IN BOOLEAN MODE) THEN 1
                      ELSE 2
                    END ASC,
                    stock DESC,
                    views DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        
        // Lier les paramètres
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
        
        // Pour LIKE, on cherche chaque tag
        $tagPattern = '%' . implode('%|%', $tags) . '%';
        $stmt->bindValue(':colorLike', $tagPattern, PDO::PARAM_STR);
        $stmt->bindValue(':sizeLike', $tagPattern, PDO::PARAM_STR);
        $stmt->bindValue(':collectionLike', $tagPattern, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sauvegarde temporaire de l'image uploadée
     * @param array $file - Fichier $_FILES
     * @return string - Chemin du fichier ou false
     */
    public function saveUploadedImage($file) {
        // Dossier de stockage temporaire
        $uploadDir = __DIR__ . '/../../tmp/uploads/';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom de fichier unique
        $fileName = 'img_' . uniqid() . '.jpg';
        $filePath = $uploadDir . $fileName;

        // Déplacer le fichier
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $filePath;
        }

        return false;
    }

    /**
     * Nettoie les fichiers temporaires
     * @param string $filePath - Chemin du fichier
     */
    public function cleanupTempFile($filePath) {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
?>
