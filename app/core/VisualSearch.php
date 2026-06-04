<?php
/**
 * VisualSearch - Classe pour recherche visuelle avec Clarifai API
 * 
 * Responsabilités:
 * - Envoyer l'image à l'API Clarifai
 * - Récupérer les tags/keywords identifiés
 * - Chercher les produits similaires dans MySQL
 * - Retourner les résultats
 */

class VisualSearch {
    
    private $config;
    private $pdo;
    private $error;
    
    /**
     * Constructeur
     * @param PDO $pdo Connexion à la base de données
     * @param array $config Configuration depuis config/visual-search.php
     */
    public function __construct($pdo, $config = []) {
        $this->pdo = $pdo;
        $this->config = $config;
        $this->error = null;
    }
    
    /**
     * === ÉTAPE 1: Appeler l'API Clarifai pour analyser l'image ===
     * 
     * L'API Clarifai reconnaît les objets/concepts dans l'image et retourne
     * une liste de tags avec un score de confiance (0-1)
     * 
     * @param string $imagePath Chemin de l'image uploadée
     * @return array|false Tableau des tags ou false en cas d'erreur
     */
    public function analyzImage($imagePath) {
        
        // Vérifier que le fichier existe
        if (!file_exists($imagePath)) {
            $this->error = "Fichier image introuvable: $imagePath";
            return false;
        }

        if (($this->config['provider'] ?? 'clarifai') === 'imagga') {
            return $this->analyzeWithImagga($imagePath);
        }
        
        // Récupérer la clé API
        $apiKey = $this->config['clarifai']['api_key'] ?? null;
        if (empty($apiKey)) {
            $this->error = "Clé API Clarifai non configurée";
            return false;
        }
        
        // === CONVERTIR L'IMAGE EN BASE64 ===
        // Clarifai accepte les images en base64
        $imageBase64 = ImageUpload::getBase64($imagePath);
        if ($imageBase64 === false) {
            $this->error = "Impossible de lire le fichier image";
            return false;
        }
        
        // === PRÉPARER LA REQUÊTE API ===
        // Structure JSON pour l'API Clarifai
        $payload = [
            'user_app_id' => [
                'user_id' => 'clarifai',
                'app_id' => 'main',
            ],
            'inputs' => [
                [
                    'data' => [
                        'image' => [
                            'base64' => $imageBase64,
                        ],
                    ],
                ],
            ],
        ];
        
        // === ENVOYER LA REQUÊTE À CLARIFAI ===
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->config['clarifai']['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Key ' . $apiKey,
            ],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Vérifier les erreurs cURL
        if ($curlError) {
            $this->error = "Erreur cURL: $curlError";
            return false;
        }
        
        // Vérifier le code HTTP
        if ($httpCode !== 200) {
            $this->error = "API Clarifai erreur: Code HTTP $httpCode";
            return false;
        }
        
        // === DÉCODER LA RÉPONSE JSON ===
        $data = json_decode($response, true);
        
        if (!isset($data['outputs'][0]['data']['concepts'])) {
            $this->error = "Réponse API invalide";
            return false;
        }
        
        // === EXTRAIRE LES TAGS AVEC SCORE > MINIMUM ===
        $tags = [];
        $minConfidence = $this->config['search']['min_confidence'] ?? 0.5;
        
        foreach ($data['outputs'][0]['data']['concepts'] as $concept) {
            $confidence = $concept['value'] ?? 0;
            
            // Garder uniquement les tags au-dessus du seuil de confiance
            if ($confidence >= $minConfidence) {
                $tags[] = [
                    'name' => $concept['name'],
                    'confidence' => round($confidence, 2),
                ];
            }
        }
        
        return $tags;
    }

    private function analyzeWithImagga($imagePath)
    {
        $apiKey = $this->config['imagga']['api_key'] ?? null;
        $apiSecret = $this->config['imagga']['api_secret'] ?? null;
        $apiUrl = $this->config['imagga']['api_url'] ?? 'https://api.imagga.com/v2/tags';

        if (empty($apiKey) || empty($apiSecret)) {
            $this->error = "Clés API Imagga non configurées";
            return false;
        }

        if (!class_exists('CURLFile')) {
            $this->error = "L'extension cURL de PHP est requise pour Imagga";
            return false;
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $apiKey . ':' . $apiSecret,
            CURLOPT_POSTFIELDS => [
                'image' => new CURLFile($imagePath),
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            $this->error = "Erreur cURL Imagga: $curlError";
            return false;
        }

        if ($httpCode !== 200) {
            $this->error = "API Imagga erreur: Code HTTP $httpCode";
            return false;
        }

        $data = json_decode($response, true);
        if (!isset($data['result']['tags']) || !is_array($data['result']['tags'])) {
            $this->error = "Réponse Imagga invalide";
            return false;
        }

        $tags = [];
        $minConfidence = $this->config['search']['min_confidence'] ?? 0.5;

        foreach ($data['result']['tags'] as $tagData) {
            $confidence = ((float)($tagData['confidence'] ?? 0)) / 100;
            $name = $tagData['tag']['en'] ?? '';

            if ($name !== '' && $confidence >= $minConfidence) {
                $tags[] = [
                    'name' => $name,
                    'confidence' => round($confidence, 2),
                ];
            }
        }

        return $tags;
    }
    
    /**
     * === ÉTAPE 2: Chercher les produits similaires dans MySQL ===
     * 
     * Utilise les tags retournés par Clarifai pour chercher dans la base de données
     * Recherche dans name, description, category
     * Utilise LIKE pour la flexibilité
     * 
     * @param array $tags Tableau des tags depuis analyzImage()
     * @return array Tableau des produits trouvés
     */
    public function findSimilarProducts($tags) {
        
        if (empty($tags)) {
            $this->error = "Aucun tag fourni";
            return [];
        }
        
        // === CONSTRUIRE LA REQUÊTE SQL ===
        // Chercher dans name, description, category
        // Utiliser LIKE pour plus de flexibilité
        
        // Structure: WHERE (name LIKE '%tag1%' OR description LIKE '%tag1%' OR category LIKE '%tag1%')
        //           OR (name LIKE '%tag2%' OR ...) ...
        
        $whereClauses = [];
        $params = [];
        
        foreach ($tags as $tag) {
            $tagName = $tag['name'];
            
            $whereClauses[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ? OR p.color LIKE ? OR p.size LIKE ? OR p.collection_name LIKE ? OR c.name LIKE ?)";
            
            for ($i = 0; $i < 7; $i++) {
                $params[] = "%$tagName%";
            }
        }
        
        // Joindre avec OR
        $whereString = "(" . implode(" OR ", $whereClauses) . ")";
        
        // === EXÉCUTER LA REQUÊTE PRÉPARÉE ===
        // Évite les injections SQL
        $maxResults = $this->config['search']['max_results'] ?? 12;
        
        $query = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    c.name AS category,
                    p.image,
                    p.description,
                    p.stock,
                    p.brand,
                    p.color,
                    p.size,
                    p.collection_name
                  FROM products p
                  LEFT JOIN categories c ON c.id = p.category_id
                  WHERE $whereString
                  AND p.stock > 0
                  AND p.is_active = 1
                  GROUP BY p.id
                  ORDER BY p.name ASC
                  LIMIT ?";
        
        $params[] = $maxResults;
        
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $products ?: [];
            
        } catch (PDOException $e) {
            $this->error = "Erreur base de données: " . $e->getMessage();
            return [];
        }
    }
    
    /**
     * === ÉTAPE 3: Fonction principale (combinée) ===
     * 
     * Combine les 2 étapes:
     * 1. Analyser l'image avec Clarifai
     * 2. Chercher les produits similaires
     * 
     * @param string $imagePath Chemin de l'image
     * @return array Tableau avec 'tags' et 'products'
     */
    public function search($imagePath) {
        
        // Étape 1: Analyser l'image
        $tags = $this->analyzImage($imagePath);
        
        if ($tags === false) {
            return [
                'success' => false,
                'error' => $this->error,
            ];
        }
        
        // Étape 2: Chercher les produits
        $products = $this->findSimilarProducts($tags);
        
        return [
            'success' => true,
            'tags' => $tags,
            'products' => $products,
            'count' => count($products),
        ];
    }
    
    /**
     * Obtenir le dernier message d'erreur
     * @return string
     */
    public function getError() {
        return $this->error;
    }
}
