<?php
/**
 * ImageUpload - Classe pour gérer l'upload et validation d'images
 * 
 * Responsabilités:
 * - Valider le fichier image
 * - Créer un nom unique
 * - Sauvegarder le fichier
 * - Retourner le chemin du fichier
 */

class ImageUpload {
    
    private $config;
    private $error;
    
    /**
     * Constructeur
     * @param array $config Configuration depuis config/visual-search.php
     */
    public function __construct($config = []) {
        $this->config = $config;
        $this->error = null;
        
        // Créer les dossiers s'ils n'existent pas
        $this->ensureDirectories();
    }
    
    /**
     * Créer les dossiers de stockage nécessaires
     */
    private function ensureDirectories() {
        $tmpDir = $this->config['upload']['tmp_dir'] ?? __DIR__ . '/../../tmp/visual-search/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }
    }
    
    /**
     * Valider et sauvegarder l'image uploadée
     * 
     * @param array $_FILES['image'] Le fichier uploadé
     * @return string|false Chemin du fichier sauvegardé ou false en cas d'erreur
     */
    public function upload($file) {
        // Vérifier si un fichier a été uploadé
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $this->error = "Aucune image uploadée ou erreur lors du transfert";
            return false;
        }
        
        // === VALIDATION DE L'EXTENSION ===
        $fileName = $file['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExt = $this->config['upload']['allowed_extensions'] ?? ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExt, $allowedExt)) {
            $this->error = "Extension non autorisée. Autorisé: " . implode(', ', $allowedExt);
            return false;
        }
        
        // === VALIDATION DE LA TAILLE ===
        $maxSize = $this->config['upload']['max_size'] ?? 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $this->error = "Fichier trop volumineux. Maximum: " . ($maxSize / 1024 / 1024) . "MB";
            return false;
        }
        
        // === VALIDATION DU MIME TYPE ===
        $allowedMime = $this->config['upload']['allowed_mime_types'] ?? ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedMime)) {
            $this->error = "Type MIME non autorisé: $mimeType";
            return false;
        }
        
        // === VALIDATION DIMENSIONS IMAGE ===
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->error = "Le fichier n'est pas une image valide";
            return false;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        $minWidth = $this->config['validation']['min_width'] ?? 100;
        $minHeight = $this->config['validation']['min_height'] ?? 100;
        $maxWidth = $this->config['validation']['max_width'] ?? 4000;
        $maxHeight = $this->config['validation']['max_height'] ?? 4000;
        
        if ($width < $minWidth || $height < $minHeight) {
            $this->error = "Image trop petite. Minimum: {$minWidth}x{$minHeight}px";
            return false;
        }
        
        if ($width > $maxWidth || $height > $maxHeight) {
            $this->error = "Image trop grande. Maximum: {$maxWidth}x{$maxHeight}px";
            return false;
        }
        
        // === GÉNÉRER UN NOM UNIQUE ===
        // Nom: md5(timestamp + random) pour éviter les collisions
        $uniqueName = md5(time() . mt_rand()) . '.' . $fileExt;
        
        // === SAUVEGARDER LE FICHIER ===
        $tmpDir = $this->config['upload']['tmp_dir'] ?? __DIR__ . '/../../tmp/visual-search/';
        $filePath = $tmpDir . $uniqueName;
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $this->error = "Erreur lors de la sauvegarde du fichier";
            return false;
        }
        
        return $filePath;
    }
    
    /**
     * Obtenir le dernier message d'erreur
     * @return string
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * Supprimer un fichier (pour nettoyage)
     * @param string $filePath Chemin du fichier
     * @return bool
     */
    public function delete($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Obtenir le contenu base64 d'une image
     * Utilisé pour envoyer à l'API Clarifai
     * 
     * @param string $filePath Chemin du fichier
     * @return string|false Contenu base64 ou false
     */
    public static function getBase64($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $imageData = file_get_contents($filePath);
        if ($imageData === false) {
            return false;
        }
        
        return base64_encode($imageData);
    }
}
