<?php
/**
 * Visual Search - Page Frontend
 * 
 * Page pour:
 * 1. Uploader une image d'un produit
 * 2. Voir un aperçu de l'image
 * 3. Lancer la recherche IA
 * 4. Voir les résultats
 */

// Démarrer la session
session_start();

// Charge la connexion DB si possible (optionnel pour la page d'accueil)
try {
    require_once __DIR__ . '/../config/pdo.php';
} catch (Exception $e) {
    // Page peut fonctionner sans DB au départ
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Visual Search - Trouvez des produits similaires</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* === UPLOAD ZONE === */
        .upload-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin-bottom: 30px;
        }
        
        .upload-zone {
            border: 3px dashed #667eea;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9ff;
        }
        
        .upload-zone:hover {
            border-color: #764ba2;
            background: #f0f2ff;
            transform: translateY(-2px);
        }
        
        .upload-zone.dragover {
            border-color: #764ba2;
            background: #e8ebff;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .upload-zone i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .upload-zone p {
            margin: 0;
            font-size: 16px;
            color: #666;
        }
        
        .upload-zone small {
            color: #999;
            display: block;
            margin-top: 10px;
        }
        
        #imageInput {
            display: none;
        }
        
        /* === IMAGE PREVIEW === */
        .preview-container {
            text-align: center;
            margin: 30px 0;
        }
        
        #imagePreview {
            max-width: 100%;
            max-height: 400px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            display: none;
        }
        
        .preview-info {
            margin-top: 15px;
            font-size: 14px;
            color: #666;
        }
        
        /* === BOUTON SEARCH === */
        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-search:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        /* === LOADING SPINNER === */
        .loading {
            display: none;
            text-align: center;
            padding: 30px;
        }
        
        .spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading p {
            margin-top: 15px;
            color: #666;
            font-weight: 600;
        }
        
        /* === TAGS AFFICHAGE === */
        .tags-container {
            margin: 30px 0;
            display: none;
        }
        
        .tags-container h5 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .tag {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            margin: 5px 5px 5px 0;
            font-size: 13px;
            font-weight: 600;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .tag-confidence {
            font-size: 11px;
            opacity: 0.9;
        }
        
        /* === RÉSULTATS PRODUITS === */
        .results-container {
            display: none;
            margin-top: 30px;
        }
        
        .results-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .results-header h4 {
            color: #667eea;
            font-weight: 700;
            margin: 0;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
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
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .product-category {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-view {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-view:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
        }
        
        .btn-add-cart {
            flex: 1;
            background: #f0f2ff;
            color: #667eea;
            border: 1px solid #667eea;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-add-cart:hover {
            background: #667eea;
            color: white;
        }
        
        /* === MESSAGE ERREUR === */
        .error-message {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
            border-left: 4px solid #ffc107;
        }
        
        .error-message i {
            margin-right: 10px;
        }
        
        /* === MESSAGE SUCCÈS === */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
            border-left: 4px solid #28a745;
        }
        
        /* === NO RESULTS === */
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-results i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        /* === FOOTER === */
        footer {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            text-align: center;
            color: #666;
            margin-top: 50px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    
<!-- === NAVBAR === -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="../home.php">
            <i class="fas fa-store"></i> E-Store
        </a>
        <span class="navbar-text">
            <i class="fas fa-search"></i> Visual Search
        </span>
    </div>
</nav>

<div class="container my-5">
    
    <!-- === TITRE === -->
    <div class="text-center mb-5">
        <h1 style="color: white; font-weight: 700; margin-bottom: 10px;">
            🔍 Recherche par Image
        </h1>
        <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px;">
            Uploadez une image d'un produit et découvrez les articles similaires
        </p>
    </div>
    
    <!-- === UPLOAD CONTAINER === -->
    <div class="upload-container">
        <form id="visualSearchForm">
            
            <!-- Upload Zone -->
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-cloud-upload-alt"></i>
                <p><strong>Cliquez ou glissez-déposez une image</strong></p>
                <small>JPG, PNG, WebP jusqu'à 5MB</small>
            </div>
            
            <input type="file" id="imageInput" name="image" accept="image/*" required>
            
            <!-- Image Preview -->
            <div class="preview-container">
                <img id="imagePreview" src="" alt="Aperçu">
                <div class="preview-info" id="previewInfo"></div>
            </div>
            
            <!-- Bouton Search -->
            <button type="submit" class="btn btn-search" id="searchBtn">
                <i class="fas fa-search"></i> Rechercher des produits similaires
            </button>
        </form>
        
        <!-- Loading Spinner -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Analyse de l'image en cours...</p>
        </div>
        
        <!-- Erreur -->
        <div class="error-message" id="errorMessage">
            <i class="fas fa-exclamation-circle"></i>
            <span id="errorText"></span>
        </div>
    </div>
    
    <!-- === TAGS RÉSULTATS === -->
    <div class="tags-container" id="tagsContainer">
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h5><i class="fas fa-tag"></i> Tags détectés</h5>
            <div id="tagsList"></div>
        </div>
    </div>
    
    <!-- === PRODUITS RÉSULTATS === -->
    <div class="results-container" id="resultsContainer">
        
        <div class="results-header">
            <h4 id="resultsTitle"></h4>
        </div>
        
        <!-- Grille Produits -->
        <div id="productsGrid"></div>
        
        <!-- Aucun résultat -->
        <div class="no-results" id="noResults" style="display: none;">
            <i class="fas fa-inbox"></i>
            <p>Aucun produit similaire trouvé</p>
            <small>Essayez une autre image</small>
        </div>
    </div>
    
</div>

<!-- === FOOTER === -->
<footer>
    <p>🚀 Visual Search - Powered by Clarifai AI</p>
</footer>

<!-- === SCRIPTS === -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ===== CONFIGURATION =====
const API_ENDPOINT = '/projet_php/e-store/public/api/visual-search.php';

// ===== ÉLÉMENTS DOM =====
const uploadZone = document.getElementById('uploadZone');
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');
const previewInfo = document.getElementById('previewInfo');
const searchBtn = document.getElementById('searchBtn');
const loading = document.getElementById('loading');
const errorMessage = document.getElementById('errorMessage');
const errorText = document.getElementById('errorText');
const tagsContainer = document.getElementById('tagsContainer');
const tagsList = document.getElementById('tagsList');
const resultsContainer = document.getElementById('resultsContainer');
const resultsTitle = document.getElementById('resultsTitle');
const productsGrid = document.getElementById('productsGrid');
const noResults = document.getElementById('noResults');
const form = document.getElementById('visualSearchForm');

// ===== DRAG & DROP =====
uploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
    uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        imageInput.files = files;
        handleImageSelect();
    }
});

// ===== CLICK UPLOAD =====
uploadZone.addEventListener('click', () => {
    imageInput.click();
});

imageInput.addEventListener('change', handleImageSelect);

// ===== AFFICHAGE PREVIEW IMAGE =====
function handleImageSelect() {
    const file = imageInput.files[0];
    
    if (!file) return;
    
    // Vérifier le type
    if (!file.type.startsWith('image/')) {
        showError('Veuillez sélectionner une image valide');
        return;
    }
    
    // Vérifier la taille
    const maxSize = 5 * 1024 * 1024; // 5MB
    if (file.size > maxSize) {
        showError('L\'image dépasse 5MB');
        imageInput.value = '';
        return;
    }
    
    // Afficher la preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.src = e.target.result;
        imagePreview.style.display = 'block';
        
        // Info fichier
        previewInfo.innerHTML = `
            <strong>${file.name}</strong> • 
            ${(file.size / 1024).toFixed(2)} KB
        `;
    };
    reader.readAsDataURL(file);
}

// ===== SUBMIT FORMULAIRE =====
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const file = imageInput.files[0];
    if (!file) {
        showError('Veuillez sélectionner une image');
        return;
    }
    
    // === CRÉER FormData pour l'upload ===
    const formData = new FormData();
    formData.append('image', file);
    
    // === MONTRER LOADING ===
    loading.style.display = 'block';
    errorMessage.style.display = 'none';
    tagsContainer.style.display = 'none';
    resultsContainer.style.display = 'none';
    searchBtn.disabled = true;
    
    try {
        // === ENVOYER À L'API ===
        const response = await fetch(API_ENDPOINT, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // === AFFICHER LES TAGS ===
            displayTags(data.tags);
            
            // === AFFICHER LES PRODUITS ===
            displayProducts(data.products, data.count);
            
        } else {
            showError(data.error || 'Erreur lors de la recherche');
        }
        
    } catch (err) {
        console.error(err);
        showError('Erreur réseau: ' + err.message);
    } finally {
        loading.style.display = 'none';
        searchBtn.disabled = false;
    }
}

);

// ===== AFFICHER LES TAGS =====
function displayTags(tags) {
    tagsList.innerHTML = '';
    
    tags.forEach(tag => {
        const confidence = (tag.confidence * 100).toFixed(0);
        const tagEl = document.createElement('div');
        tagEl.className = 'tag';
        tagEl.innerHTML = `
            <strong>${tag.name}</strong>
            <span class="tag-confidence">${confidence}%</span>
        `;
        tagsList.appendChild(tagEl);
    });
    
    tagsContainer.style.display = 'block';
}

// ===== AFFICHER LES PRODUITS =====
function displayProducts(products, count) {
    resultsTitle.textContent = `${count} produit${count !== 1 ? 's' : ''} trouvé${count !== 1 ? 's' : ''}`;
    
    if (count === 0) {
        noResults.style.display = 'block';
        productsGrid.innerHTML = '';
        resultsContainer.style.display = 'block';
        return;
    }
    
    noResults.style.display = 'none';
    productsGrid.innerHTML = '';
    
    products.forEach((product, index) => {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.style.animationDelay = `${index * 0.1}s`;
        
        // Image produit
        const image = product.image ? `../uploads/products/${product.image}` : 'https://via.placeholder.com/250x200?text=No+Image';
        
        card.innerHTML = `
            <img src="${image}" alt="${product.name}" class="product-image" onerror="this.src='https://via.placeholder.com/250x200?text=No+Image'">
            <div class="product-info">
                <div class="product-name">${product.name}</div>
                <div class="product-category">${product.category}</div>
                <div class="product-price">€${parseFloat(product.price).toFixed(2)}</div>
                <div class="product-actions">
                    <a href="./product.php?id=${product.id}" class="btn-view">
                        <i class="fas fa-eye"></i> Voir
                    </a>
                    <button class="btn-add-cart" onclick="addToCart(${product.id})">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                </div>
            </div>
        `;
        
        productsGrid.appendChild(card);
    });
    
    resultsContainer.style.display = 'block';
}

// ===== AFFICHER ERREUR =====
function showError(message) {
    errorText.textContent = message;
    errorMessage.style.display = 'block';
    resultsContainer.style.display = 'none';
    tagsContainer.style.display = 'none';
}

// ===== AJOUTER AU PANIER =====
function addToCart(productId) {
    // À implémenter selon votre système de panier
    alert('Ajouté au panier!');
}
</script>

</body>
</html>
