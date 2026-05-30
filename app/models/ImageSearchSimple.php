<?php
/**
 * Alternative simple : ImageSearch sans API externe
 * Utilise les métadonnées de l'image et heuristiques simples
 * 
 * SOLUTION DE SECOURS si Clarifai ne fonctionne pas
 */

class ImageSearchSimple {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Valide le fichier image
     */
    public function validateImageFile($file) {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Erreur lors du chargement.'];
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'Fichier trop volumineux (max 5MB).'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Format non autorisé.'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Analyse simple : couleurs dominantes + dimensions
     * Utilise getimagesize() et fonction PHP d'extraction de couleurs
     */
    public function analyzeImageSimple($imagePath) {
        $tags = [];

        // 1. Obtenir les dimensions de l'image
        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo) {
            return [];
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Analyser les dimensions
        $aspectRatio = $width / $height;
        
        if ($aspectRatio > 1.3) {
            $tags[] = 'landscape';
            $tags[] = 'wide';
        } elseif ($aspectRatio < 0.77) {
            $tags[] = 'portrait';
            $tags[] = 'tall';
        } else {
            $tags[] = 'square';
        }

        // 2. Extraire les couleurs dominantes (simple)
        $colors = $this->extractDominantColors($imagePath);
        $tags = array_merge($tags, $colors);

        // 3. Heuristique simple : détecter des motifs
        $patterns = $this->detectPatterns($imagePath);
        $tags = array_merge($tags, $patterns);

        // Retirer les doublons et retourner
        return array_unique($tags);
    }

    /**
     * Extrait les couleurs dominantes (très basique)
     */
    private function extractDominantColors($imagePath) {
        $colors = [];

        try {
            // Créer une image redimensionnée pour l'analyse
            $img = @imagecreatefromjpeg($imagePath);
            if (!$img) {
                $img = @imagecreatefrompng($imagePath);
            }
            if (!$img) {
                return [];
            }

            // Redimensionner à 10x10 pour analyse rapide
            $thumb = imagecreatetruecolor(10, 10);
            imagecopyresampled($thumb, $img, 0, 0, 0, 0, 10, 10, imagesx($img), imagesy($img));

            // Analyser les pixels
            $colorMap = [];
            for ($x = 0; $x < 10; $x++) {
                for ($y = 0; $y < 10; $y++) {
                    $rgb = imagecolorat($thumb, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Classer les couleurs
                    $colorName = $this->rgbToColorName($r, $g, $b);
                    if ($colorName) {
                        $colorMap[$colorName] = ($colorMap[$colorName] ?? 0) + 1;
                    }
                }
            }

            // Retourner les 3 couleurs les plus fréquentes
            arsort($colorMap);
            $colors = array_slice(array_keys($colorMap), 0, 3);

            imagedestroy($img);
            imagedestroy($thumb);

        } catch (Exception $e) {
            // Silencieusement ignorer les erreurs
        }

        return $colors;
    }

    /**
     * Convertir RGB en nom de couleur
     */
    private function rgbToColorName($r, $g, $b) {
        // Calculer la luminosité
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        // Trop clair = blanc
        if ($luminance > 0.9) {
            return 'white';
        }
        
        // Trop foncé = noir
        if ($luminance < 0.1) {
            return 'black';
        }

        // Trouver la teinte dominante
        $hue = $this->rgbToHue($r, $g, $b);

        if ($hue < 15 || $hue > 345) return 'red';
        if ($hue >= 15 && $hue < 45) return 'orange';
        if ($hue >= 45 && $hue < 75) return 'yellow';
        if ($hue >= 75 && $hue < 165) return 'green';
        if ($hue >= 165 && $hue < 255) return 'blue';
        if ($hue >= 255 && $hue <= 345) return 'purple';

        return null;
    }

    /**
     * Calculer la teinte HSL
     */
    private function rgbToHue($r, $g, $b) {
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        if ($delta == 0) {
            $hue = 0;
        } elseif ($max == $r) {
            $hue = 60 * (($g - $b) / $delta % 6);
        } elseif ($max == $g) {
            $hue = 60 * (($b - $r) / $delta + 2);
        } else {
            $hue = 60 * (($r - $g) / $delta + 4);
        }

        if ($hue < 0) $hue += 360;

        return $hue;
    }

    /**
     * Détection de motifs simples (contraste, texture)
     */
    private function detectPatterns($imagePath) {
        $patterns = [];

        try {
            $img = @imagecreatefromjpeg($imagePath);
            if (!$img) {
                $img = @imagecreatefrompng($imagePath);
            }
            if (!$img) {
                return [];
            }

            // Redimensionner pour analyse
            $thumb = imagecreatetruecolor(20, 20);
            imagecopyresampled($thumb, $img, 0, 0, 0, 0, 20, 20, imagesx($img), imagesy($img));

            // Calculer le contraste
            $contrast = $this->calculateContrast($thumb);

            if ($contrast > 0.7) {
                $patterns[] = 'high_contrast';
                $patterns[] = 'patterns';
            } else {
                $patterns[] = 'smooth';
            }

            imagedestroy($img);
            imagedestroy($thumb);

        } catch (Exception $e) {
            // Ignorer
        }

        return $patterns;
    }

    /**
     * Calculer le contraste de l'image
     */
    private function calculateContrast($img) {
        $sum = 0;
        $count = 0;

        for ($x = 0; $x < imagesx($img); $x++) {
            for ($y = 0; $y < imagesy($img); $y++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b);
                $sum += $luminance;
                $count++;
            }
        }

        $average = $sum / $count;
        $variance = 0;

        for ($x = 0; $x < imagesx($img); $x++) {
            for ($y = 0; $y < imagesy($img); $y++) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b);
                $variance += pow($luminance - $average, 2);
            }
        }

        $variance /= $count;
        return sqrt($variance) / 255;
    }

    /**
     * Chercher les produits basés sur les tags
     */
    public function searchSimilarProducts($tags, $limit = 12) {
        if (empty($tags)) {
            return [];
        }

        // Créer les conditions OR pour chaque tag
        $conditions = [];
        $params = [];

        foreach ($tags as $i => $tag) {
            $placeholder = ':tag' . $i;
            $conditions[] = "p.color LIKE $placeholder";
            $params[$placeholder] = '%' . $tag . '%';
        }

        $whereClause = implode(' OR ', $conditions);

        $query = "SELECT * FROM products 
                  WHERE is_active = 1 
                  AND stock > 0
                  AND ($whereClause)
                  ORDER BY stock DESC, views DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Sauvegarder l'image temporaire
     */
    public function saveUploadedImage($file) {
        $uploadDir = __DIR__ . '/../../tmp/uploads/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = 'img_' . uniqid() . '.jpg';
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $filePath;
        }

        return false;
    }

    /**
     * Nettoyer le fichier temporaire
     */
    public function cleanupTempFile($filePath) {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
?>
