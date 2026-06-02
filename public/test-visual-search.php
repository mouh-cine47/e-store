<?php
// Test simple pour vérifier que Apache fonctionne

echo "✅ APACHE FONCTIONNE!\n\n";

// Vérifier les fichiers existants
$files = [
    'config/visual-search.php',
    'app/core/ImageUpload.php',
    'app/core/VisualSearch.php',
    'public/api/visual-search.php',
];

echo "📂 Fichiers Visual Search:\n";
foreach ($files as $file) {
    $path = __DIR__ . '/../' . $file;
    if (file_exists($path)) {
        echo "  ✅ $file\n";
    } else {
        echo "  ❌ $file (MANQUANT)\n";
    }
}

// Vérifier le .env
echo "\n⚙️ Configuration:\n";
if (file_exists(__DIR__ . '/../.env')) {
    echo "  ✅ .env existe\n";
    $env = parse_ini_file(__DIR__ . '/../.env');
    echo "  ✅ CLARIFAI_API_KEY: " . (empty($env['CLARIFAI_API_KEY']) ? "❌ VIDE" : "✅ Configurée") . "\n";
} else {
    echo "  ❌ .env manquant\n";
}

// Vérifier les dossiers
echo "\n📁 Dossiers:\n";
$dirs = ['tmp/visual-search', 'public/uploads/visual-search', 'public/api'];
foreach ($dirs as $dir) {
    $path = __DIR__ . '/../' . $dir;
    if (is_dir($path)) {
        echo "  ✅ $dir\n";
    } else {
        echo "  ❌ $dir (MANQUANT)\n";
    }
}

// Vérifier les classes
echo "\n🔧 Classes:\n";
try {
    require_once __DIR__ . '/../app/core/ImageUpload.php';
    echo "  ✅ ImageUpload chargée\n";
} catch (Exception $e) {
    echo "  ❌ ImageUpload: " . $e->getMessage() . "\n";
}

try {
    require_once __DIR__ . '/../app/core/VisualSearch.php';
    echo "  ✅ VisualSearch chargée\n";
} catch (Exception $e) {
    echo "  ❌ VisualSearch: " . $e->getMessage() . "\n";
}

echo "\n✅ Test complété!\n\n";

echo "👉 Accéder à: http://localhost/projet_php/e-store/public/visual-search.php\n";
?>
