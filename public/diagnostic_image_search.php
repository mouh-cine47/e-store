<?php
/**
 * Page de diagnostic - Vérifier que tout fonctionne
 * URL: /public/diagnostic_image_search.php
 */

require_once __DIR__ . '/../config/pdo.php';
require_once __DIR__ . '/../config/image_search_config.php';

// Récupérer les diagnostics
$diag = getImageSearchDiagnostics();
$errors = validateImageSearchConfig();

// Vérifier la DB
$hasProductsTable = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
    $hasProductsTable = (bool)$stmt->fetch();
} catch (Exception $e) {
    // Erreur DB
}

$productCount = 0;
if ($hasProductsTable) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND stock > 0");
        $result = $stmt->fetch();
        $productCount = $result['count'];
    } catch (Exception $e) {
        // Erreur
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic - Recherche par Image</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 30px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        .status {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 12px;
            border-left: 4px solid #ccc;
            background: #f9f9f9;
        }
        
        .status.ok {
            border-left-color: #4caf50;
            background: #e8f5e9;
        }
        
        .status.error {
            border-left-color: #f44336;
            background: #ffebee;
        }
        
        .status.warning {
            border-left-color: #ff9800;
            background: #fff3e0;
        }
        
        .status-icon {
            font-size: 1.5em;
            margin-right: 15px;
            min-width: 25px;
        }
        
        .status-text {
            flex: 1;
        }
        
        .status-label {
            font-weight: bold;
            color: #333;
        }
        
        .status-value {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .config-box {
            background: #f0f0f0;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            overflow-x: auto;
        }
        
        .next-steps {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
        }
        
        .next-steps h3 {
            margin-top: 0;
            color: #1976d2;
        }
        
        .next-steps ol {
            margin: 10px 0;
        }
        
        .next-steps li {
            margin: 8px 0;
            color: #333;
        }
        
        a {
            color: #2196f3;
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic - Recherche par Image</h1>
        <p>Cette page teste la configuration de votre système de recherche par image.</p>

        <!-- Méthode activée -->
        <h2>Configuration Active</h2>
        <div class="status ok">
            <div class="status-icon">⚙️</div>
            <div class="status-text">
                <div class="status-label">Méthode activée</div>
                <div class="status-value">
                    <?php if ($diag['method'] === 'clarifai'): ?>
                        <strong>Clarifai AI</strong> (reconnaissance d'objets cloud)
                    <?php else: ?>
                        <strong>Analyse Simple</strong> (couleurs + dimensions, pas d'API)
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Erreurs de configuration -->
        <?php if (!empty($errors)): ?>
            <h2>⚠️ Erreurs de Configuration</h2>
            <?php foreach ($errors as $error): ?>
                <div class="status error">
                    <div class="status-icon">❌</div>
                    <div class="status-text">
                        <div class="status-value"><?php echo htmlspecialchars($error); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Système PHP -->
        <h2>🖥️ Système PHP</h2>
        
        <div class="status <?php echo $diag['php_gd_enabled'] ? 'ok' : 'error'; ?>">
            <div class="status-icon"><?php echo $diag['php_gd_enabled'] ? '✅' : '❌'; ?></div>
            <div class="status-text">
                <div class="status-label">GD Library (traitement d'images)</div>
                <div class="status-value">
                    <?php echo $diag['php_gd_enabled'] ? 'Activée' : 'Désactivée - Analyse simple impossible'; ?>
                </div>
            </div>
        </div>

        <div class="status <?php echo $diag['php_curl_enabled'] ? 'ok' : 'error'; ?>">
            <div class="status-icon"><?php echo $diag['php_curl_enabled'] ? '✅' : '❌'; ?></div>
            <div class="status-text">
                <div class="status-label">cURL (requêtes HTTP)</div>
                <div class="status-value">
                    <?php echo $diag['php_curl_enabled'] ? 'Activée' : 'Désactivée - Clarifai impossible'; ?>
                </div>
            </div>
        </div>

        <!-- Fichiers et dossiers -->
        <h2>📁 Fichiers et Dossiers</h2>
        
        <div class="status <?php echo $diag['upload_dir_exists'] ? 'ok' : 'error'; ?>">
            <div class="status-icon"><?php echo $diag['upload_dir_exists'] ? '✅' : '❌'; ?></div>
            <div class="status-text">
                <div class="status-label">Dossier d'upload</div>
                <div class="status-value">
                    <code><?php echo UPLOAD_DIR; ?></code>
                    <?php echo $diag['upload_dir_exists'] ? '- Existe' : '- À créer'; ?>
                </div>
            </div>
        </div>

        <div class="status <?php echo ($diag['upload_dir_exists'] && $diag['upload_dir_writable']) ? 'ok' : 'error'; ?>">
            <div class="status-icon"><?php echo ($diag['upload_dir_exists'] && $diag['upload_dir_writable']) ? '✅' : '❌'; ?></div>
            <div class="status-text">
                <div class="status-label">Permissions d'écriture</div>
                <div class="status-value">
                    <?php echo ($diag['upload_dir_exists'] && $diag['upload_dir_writable']) ? 'Dossier accessible' : 'Impossible d\'écrire dans le dossier'; ?>
                </div>
            </div>
        </div>

        <!-- Base de données -->
        <h2>🗄️ Base de Données</h2>
        
        <div class="status <?php echo $hasProductsTable ? 'ok' : 'error'; ?>">
            <div class="status-icon"><?php echo $hasProductsTable ? '✅' : '❌'; ?></div>
            <div class="status-text">
                <div class="status-label">Table products</div>
                <div class="status-value">
                    <?php echo $hasProductsTable ? 'Trouvée' : 'Manquante - Importez database.sql'; ?>
                </div>
            </div>
        </div>

        <?php if ($hasProductsTable): ?>
            <div class="status <?php echo $productCount > 0 ? 'ok' : 'warning'; ?>">
                <div class="status-icon"><?php echo $productCount > 0 ? '✅' : '⚠️'; ?></div>
                <div class="status-text">
                    <div class="status-label">Produits disponibles</div>
                    <div class="status-value">
                        <strong><?php echo $productCount; ?></strong> produits actifs avec stock
                        <?php echo $productCount == 0 ? '(aucun résultat ne sera trouvé)' : ''; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Configuration Clarifai -->
        <?php if ($diag['method'] === 'clarifai'): ?>
            <h2>🚀 Configuration Clarifai</h2>
            
            <div class="status <?php echo (isset($diag['clarifai_api_key_set']) && $diag['clarifai_api_key_set']) ? 'ok' : 'error'; ?>">
                <div class="status-icon"><?php echo (isset($diag['clarifai_api_key_set']) && $diag['clarifai_api_key_set']) ? '✅' : '❌'; ?></div>
                <div class="status-text">
                    <div class="status-label">Clé API Clarifai</div>
                    <div class="status-value">
                        <?php if (isset($diag['clarifai_api_key_set']) && $diag['clarifai_api_key_set']): ?>
                            Clé configurée
                        <?php else: ?>
                            Clé non configurée - Mettez à jour <code>config/image_search_config.php</code>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="status ok">
                <div class="status-icon">🔗</div>
                <div class="status-text">
                    <div class="status-label">Endpoint Clarifai</div>
                    <div class="status-value"><code><?php echo CLARIFAI_API_URL; ?></code></div>
                </div>
            </div>
        <?php else: ?>
            <h2>📊 Configuration Analyse Simple</h2>
            <p>Cette méthode n'utilise pas d'API externe.</p>
            <ul>
                <li>✅ Pas de clé API requise</li>
                <li>✅ Pas de limite de requêtes</li>
                <li>✅ Fonctionne offline</li>
                <li>⚠️ Moins précis que Clarifai (basé sur couleurs)</li>
            </ul>
        <?php endif; ?>

        <!-- Actions recommandées -->
        <h2>📝 Prochaines Étapes</h2>
        
        <?php if (!empty($errors) || $productCount == 0 || !$hasProductsTable): ?>
            <div class="next-steps">
                <h3>À faire :</h3>
                <ol>
                    <?php if (!$hasProductsTable): ?>
                        <li>Importer la base de données : <code>database.sql</code></li>
                    <?php endif; ?>
                    
                    <?php if ($productCount == 0 && $hasProductsTable): ?>
                        <li>Ajouter des produits à votre base de données (au moins 5)</li>
                    <?php endif; ?>
                    
                    <?php if ($diag['method'] === 'clarifai' && isset($diag['clarifai_api_key_set']) && !$diag['clarifai_api_key_set']): ?>
                        <li>Créer un compte sur <a href="https://clarifai.com" target="_blank">Clarifai.com</a></li>
                        <li>Générer une clé API</li>
                        <li>Mettre à jour <code>config/image_search_config.php</code> avec votre clé</li>
                    <?php endif; ?>
                </ol>
            </div>
        <?php else: ?>
            <div class="next-steps">
                <h3>✅ Vous êtes prêt !</h3>
                <ol>
                    <li>Allez à <a href="search_by_image.php">Recherche par Image</a></li>
                    <li>Uploadez une image d'un produit</li>
                    <li>Cliquez sur "Rechercher"</li>
                    <li>Voyez les produits similaires s'afficher</li>
                </ol>
            </div>
        <?php endif; ?>

        <!-- Fichiers config -->
        <h2>🔧 Configuration Détaillée</h2>
        <p>Fichiers à modifier :</p>
        <div class="config-box">
            <strong>config/image_search_config.php</strong><br>
            Contrôle la méthode et les paramètres
        </div>
        <div class="config-box">
            <strong>config/clarifai.php</strong><br>
            (Ancienne config - utilisez image_search_config.php)
        </div>

        <!-- Footer -->
        <hr style="margin-top: 40px;">
        <p style="color: #999; font-size: 0.9em;">
            Page de diagnostic | 
            <a href="search_by_image.php">Retour à la recherche</a>
        </p>
    </div>
</body>
</html>
