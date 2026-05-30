<?php
/**
 * Page de recherche par image
 * URL: /public/search_by_image.php
 */

require_once __DIR__ . '/../config/pdo.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Récupérer les catégories pour le filtre
$query = "SELECT id, name FROM categories ORDER BY name";
$stmt = $pdo->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rechercher par Image | E-Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Styles spécifiques pour la recherche par image */
        .image-search-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }

        .search-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .search-header h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 10px;
        }

        .search-header p {
            color: #666;
            font-size: 1.1em;
        }

        /* Upload area */
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: #fafafa;
            margin-bottom: 30px;
        }

        .upload-area:hover {
            border-color: #333;
            background-color: #f5f5f5;
        }

        .upload-area.dragover {
            border-color: #ff6b6b;
            background-color: #fff5f5;
        }

        .upload-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .upload-text h3 {
            margin: 10px 0;
            color: #333;
        }

        .upload-text p {
            color: #999;
            margin: 5px 0;
        }

        #imageInput {
            display: none;
        }

        /* Aperçu de l'image */
        .preview-container {
            margin: 30px 0;
            display: none;
        }

        .preview-container.show {
            display: block;
        }

        .image-preview {
            max-width: 300px;
            max-height: 300px;
            margin: 0 auto 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Boutons */
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .btn-search {
            background-color: #333;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        .btn-search:hover {
            background-color: #555;
        }

        .btn-search:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .btn-reset {
            background-color: #ddd;
            color: #333;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .btn-reset:hover {
            background-color: #bbb;
        }

        /* Loading spinner */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading.show {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #333;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Tags et résultats */
        .tags-container {
            margin: 30px 0;
            display: none;
        }

        .tags-container.show {
            display: block;
        }

        .tags-list {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .tags-list h4 {
            margin-top: 0;
            color: #333;
        }

        .tag {
            display: inline-block;
            background: #333;
            color: white;
            padding: 5px 12px;
            margin: 5px 5px 5px 0;
            border-radius: 20px;
            font-size: 0.9em;
        }

        /* Produits résultats */
        .results-container {
            display: none;
        }

        .results-container.show {
            display: block;
        }

        .results-header {
            margin: 30px 0 20px 0;
            font-size: 1.3em;
            color: #333;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f5f5f5;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .product-brand {
            color: #999;
            font-size: 0.9em;
            margin-bottom: 8px;
        }

        .product-price {
            color: #ff6b6b;
            font-weight: bold;
            font-size: 1.1em;
        }

        /* Messages d'erreur */
        .error-message {
            background-color: #fff5f5;
            color: #c92a2a;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        /* Message vide */
        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            display: none;
        }

        .no-results.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <!-- Contenu principal -->
    <main class="image-search-container">
        <div class="search-header">
            <h1>🔍 Rechercher par Image</h1>
            <p>Uploadez une image et trouvez les produits similaires</p>
        </div>

        <!-- Upload Area -->
        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">📸</div>
            <div class="upload-text">
                <h3>Glissez votre image ici</h3>
                <p>ou cliquez pour parcourir</p>
                <p style="font-size: 0.9em; margin-top: 10px;">JPG, PNG ou WebP • Max 5MB</p>
            </div>
            <input type="file" id="imageInput" accept="image/jpeg,image/png,image/webp">
        </div>

        <!-- Aperçu de l'image -->
        <div class="preview-container" id="previewContainer">
            <img id="imagePreview" class="image-preview" alt="Aperçu">
            <div class="button-group">
                <button class="btn-search" id="searchBtn">🔍 Rechercher</button>
                <button class="btn-reset" id="resetBtn">✕ Nouvelle image</button>
            </div>
        </div>

        <!-- Loading spinner -->
        <div class="loading" id="loadingSpinner">
            <div class="spinner"></div>
            <p>Analyse en cours...</p>
        </div>

        <!-- Message d'erreur -->
        <div class="error-message" id="errorMessage"></div>

        <!-- Tags détectés -->
        <div class="tags-container" id="tagsContainer">
            <div class="tags-list">
                <h4>🏷️ Tags détectés dans l'image :</h4>
                <div id="tagsList"></div>
            </div>
        </div>

        <!-- Résultats -->
        <div class="results-container" id="resultsContainer">
            <div class="results-header" id="resultsHeader"></div>
            <div class="products-grid" id="productsGrid"></div>
            <div class="no-results" id="noResults">
                <p>Aucun produit correspondant trouvé 😞</p>
                <p style="font-size: 0.9em; color: #ccc;">Essayez une autre image</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <script>
        // === Configuration ===
        const uploadArea = document.getElementById('uploadArea');
        const imageInput = document.getElementById('imageInput');
        const previewContainer = document.getElementById('previewContainer');
        const imagePreview = document.getElementById('imagePreview');
        const searchBtn = document.getElementById('searchBtn');
        const resetBtn = document.getElementById('resetBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const errorMessage = document.getElementById('errorMessage');
        const tagsContainer = document.getElementById('tagsContainer');
        const tagsList = document.getElementById('tagsList');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultsHeader = document.getElementById('resultsHeader');
        const productsGrid = document.getElementById('productsGrid');
        const noResults = document.getElementById('noResults');

        // === Drag & Drop ===
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleImageSelect(files[0]);
            }
        });

        // === Click pour parcourir ===
        uploadArea.addEventListener('click', () => {
            imageInput.click();
        });

        imageInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleImageSelect(e.target.files[0]);
            }
        });

        // === Traiter la sélection d'image ===
        function handleImageSelect(file) {
            // Vérifier le type
            if (!file.type.startsWith('image/')) {
                showError('Veuillez sélectionner une image valide.');
                return;
            }

            // Vérifier la taille
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                showError('L\'image est trop volumineux (max 5MB).');
                return;
            }

            // Afficher l'aperçu
            const reader = new FileReader();
            reader.onload = (e) => {
                imagePreview.src = e.target.result;
                previewContainer.classList.add('show');
                hideError();
                hideResults();
            };
            reader.readAsDataURL(file);
        }

        // === Rechercher les produits ===
        searchBtn.addEventListener('click', async () => {
            const file = imageInput.files[0];
            if (!file) {
                showError('Veuillez sélectionner une image.');
                return;
            }

            // Préparer les données
            const formData = new FormData();
            formData.append('image', file);

            // Afficher le loader
            showLoading();
            hideError();
            hideResults();

            try {
                // Envoyer la requête
                const response = await fetch('./api_image_search.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                hideLoading();

                if (!response.ok) {
                    showError(data.error || 'Une erreur est survenue.');
                    return;
                }

                // Afficher les tags
                displayTags(data.tags);

                // Afficher les résultats
                displayResults(data.products, data.count);

            } catch (error) {
                hideLoading();
                showError('Erreur réseau: ' + error.message);
                console.error('Erreur:', error);
            }
        });

        // === Réinitialiser ===
        resetBtn.addEventListener('click', () => {
            imageInput.value = '';
            imagePreview.src = '';
            previewContainer.classList.remove('show');
            hideResults();
            hideError();
        });

        // === Afficher les tags ===
        function displayTags(tags) {
            tagsList.innerHTML = tags.map(tag => 
                `<span class="tag">${tag}</span>`
            ).join('');
            tagsContainer.classList.add('show');
        }

        // === Afficher les résultats ===
        function displayResults(products, count) {
            resultsHeader.textContent = `✅ ${count} produit(s) trouvé(s)`;
            resultsContainer.classList.add('show');

            if (count === 0) {
                noResults.classList.add('show');
                productsGrid.innerHTML = '';
                return;
            }

            noResults.classList.remove('show');
            productsGrid.innerHTML = products.map(product => `
                <a href="./product.php?id=${product.id}" class="product-card">
                    <img src="../${product.image || 'assets/images/placeholder.jpg'}" alt="${product.name}" class="product-image">
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-brand">${product.brand || 'Sans marque'}</div>
                        <div class="product-price">${parseFloat(product.price).toFixed(2)} €</div>
                    </div>
                </a>
            `).join('');
        }

        // === Fonctions utilitaires ===
        function showLoading() {
            loadingSpinner.classList.add('show');
        }

        function hideLoading() {
            loadingSpinner.classList.remove('show');
        }

        function showError(message) {
            errorMessage.textContent = '❌ ' + message;
            errorMessage.classList.add('show');
        }

        function hideError() {
            errorMessage.classList.remove('show');
        }

        function hideResults() {
            tagsContainer.classList.remove('show');
            resultsContainer.classList.remove('show');
        }
    </script>
</body>
</html>
