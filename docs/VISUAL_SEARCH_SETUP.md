# 🔍 VISUAL SEARCH - Guide d'Installation Complet

> Fonctionnalité AI pour rechercher des produits à partir d'une image

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Installation](#installation)
3. [Configuration Clarifai](#configuration-clarifai)
4. [Utilisation](#utilisation)
5. [Architecture](#architecture)
6. [Dépannage](#dépannage)
7. [Sécurité](#sécurité)

---

## ✅ Prérequis

Avant de commencer, vérifiez que vous avez :

- ✅ **PHP 7.4+** (vérifier: `php -v`)
- ✅ **MySQL 5.7+** (XAMPP inclus)
- ✅ **Apache** (XAMPP inclus)
- ✅ **Extension cURL PHP** (pour les appels API)
- ✅ **Composer** (optionnel mais recommandé)
- ✅ **Compte Clarifai gratuit** (5000 appels/mois)

### Vérifier les extensions PHP

```bash
php -m | findstr curl
php -m | findstr gd
php -m | findstr json
```

Si cURL n'est pas activé, éditer `php.ini` :
```ini
extension=curl
```

---

## 🚀 Installation

### Étape 1: Créer les dossiers nécessaires

```bash
# Depuis la racine du projet
mkdir -p tmp/visual-search
mkdir -p public/uploads/visual-search
mkdir -p public/api
```

### Étape 2: Vérifier les permissions

Les dossiers doivent avoir les permissions 755:

```bash
# Sur Windows (XAMPP)
# Généralement automatique, mais vérifier:
icacls "tmp/visual-search" /grant Users:F /T
icacls "public/uploads/visual-search" /grant Users:F /T
```

### Étape 3: Copier le fichier .env

```bash
# Dans la racine du projet
cp .env.visual-search.example .env
```

Éditer le `.env` et ajouter votre clé API Clarifai.

### Étape 4: Vérifier les fichiers créés

Vous devez avoir :

```
e-store/
├── config/
│   └── visual-search.php          ✅ Créé
├── app/core/
│   ├── ImageUpload.php            ✅ Créé
│   └── VisualSearch.php           ✅ Créé
├── public/
│   ├── visual-search.php          ✅ Créé
│   ├── api/
│   │   └── visual-search.php      ✅ Créé
│   └── uploads/
│       └── visual-search/         ✅ Dossier
├── tmp/
│   └── visual-search/             ✅ Dossier
└── .env                           ✅ Configuration
```

---

## 🔑 Configuration Clarifai

### Étape 1: Créer un compte Clarifai

1. Aller sur: **https://clarifai.com**
2. Cliquer sur **"Sign Up"**
3. Remplir le formulaire
4. Confirmer votre email

### Étape 2: Créer une application

1. Aller sur: **https://clarifai.com/applications**
2. Cliquer sur **"Create New Application"**
3. Remplir les détails:
   - **Application Name**: `e-store-visual-search`
   - **Default Base Workflow**: `General`
4. Cliquer sur **Create Application**

### Étape 3: Obtenir la clé API

1. Aller sur: **https://clarifai.com/settings/applications**
2. Sélectionner votre application
3. Aller sur l'onglet **"Keys"**
4. Copier votre **Personal Access Token**

### Étape 4: Configurer le .env

Éditer `.env` à la racine du projet:

```env
CLARIFAI_API_KEY=abc123xyz_votre_clé_complète_ici
CLARIFAI_API_URL=https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs
```

⚠️ **IMPORTANT**: Ne pas commiter le `.env` sur GitHub!

Vérifier `.gitignore` contient:
```
.env
```

---

## 🎯 Utilisation

### Accéder à Visual Search

#### Option 1: URL directe
```
http://localhost/projet_php/e-store/public/visual-search.php
```

#### Option 2: Ajouter le lien dans la navbar

Éditer `includes/header.php` et ajouter:

```html
<li class="nav-item">
    <a class="nav-link" href="/projet_php/e-store/public/visual-search.php">
        <i class="fas fa-search"></i> Visual Search
    </a>
</li>
```

### Utiliser la page

1. **Uploader une image**
   - Cliquer ou glisser-déposer une image
   - Formats autorisés: JPG, PNG, WebP
   - Maximum 5MB

2. **Voir l'aperçu**
   - L'image s'affiche dans le preview

3. **Cliquer "Rechercher"**
   - Attendre l'analyse IA (2-5 secondes)
   - Voir les tags détectés
   - Voir les produits similaires

4. **Interagir avec les résultats**
   - Cliquer "Voir" pour les détails
   - Ajouter au panier avec l'icône
   - Nouvelle recherche quand on veut

---

## 🏗️ Architecture

### Structure du projet

```
Visual Search Flow:
│
├── 1️⃣ Frontend: /public/visual-search.php
│   ├── Upload image (HTML form + Drag&drop)
│   ├── Preview image (JavaScript)
│   └── Send to API (AJAX/fetch)
│
├── 2️⃣ Backend API: /public/api/visual-search.php
│   ├── Recevoir le fichier
│   ├── Valider sécurité
│   ├── Sauvegarder temporairement
│   └── Appeler les classes
│
├── 3️⃣ Image Upload: /app/core/ImageUpload.php
│   ├── Valider l'extension
│   ├── Valider la taille
│   ├── Valider le mime type
│   ├── Valider les dimensions
│   └── Sauvegarder le fichier
│
├── 4️⃣ Visual Search: /app/core/VisualSearch.php
│   ├── Appeler API Clarifai
│   ├── Recevoir les tags
│   ├── Chercher dans MySQL
│   └── Retourner les produits
│
└── 5️⃣ Retour Frontend
    ├── Afficher les tags
    ├── Afficher les produits
    └── Animations
```

### Classes PHP

#### ImageUpload.php
- `upload($file)` - Upload et valider
- `delete($path)` - Supprimer le fichier
- `getBase64($path)` - Convertir en base64

#### VisualSearch.php
- `analyzImage($path)` - Appel API Clarifai
- `findSimilarProducts($tags)` - Recherche MySQL
- `search($path)` - Fonction principale

### Flux de données

```
Image uploadée
    ↓
ImageUpload::upload() - Validation
    ↓
VisualSearch::analyzImage() - API Clarifai → Tags
    ↓
VisualSearch::findSimilarProducts() - MySQL LIKE
    ↓
JSON response
    ↓
Frontend affiche tags + produits
```

---

## 🔍 Détails techniques

### Configuration (config/visual-search.php)

```php
// Clarifai API
'clarifai' => [
    'api_key' => 'votre_clé',
    'api_url' => 'https://api.clarifai.com/...',
]

// Fichiers upload
'upload' => [
    'tmp_dir' => '/tmp/visual-search/',
    'allowed_extensions' => ['jpg', 'png', 'webp'],
    'max_size' => 5 * 1024 * 1024,
    'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp'],
]

// Recherche produits
'search' => [
    'max_results' => 12,
    'min_confidence' => 0.5,
    'search_fields' => ['name', 'description', 'category'],
]
```

### API Clarifai

L'API Clarifai retourne les concepts (tags) détectés:

```json
{
    "outputs": [
        {
            "data": {
                "concepts": [
                    {"name": "shirt", "value": 0.95},
                    {"name": "red", "value": 0.87},
                    {"name": "fabric", "value": 0.76},
                    ...
                ]
            }
        }
    ]
}
```

### Recherche MySQL

La recherche utilise les tags pour chercher dans:
- `products.name` LIKE '%shirt%'
- `products.description` LIKE '%shirt%'
- `products.category` LIKE '%shirt%'

---

## ❌ Dépannage

### "Clé API non configurée"

**Problème**: `Error: API key not configured`

**Solutions**:
1. Vérifier `.env` existe
2. Vérifier `CLARIFAI_API_KEY` est rempli
3. Vérifier pas d'espaces avant/après la clé
4. Redémarrer Apache après modification

```bash
# Vérifier
cat .env | grep CLARIFAI_API_KEY
```

### "Erreur 401 Unauthorized"

**Problème**: API retourne 401 ou 403

**Solutions**:
1. Vérifier que la clé est correcte
2. Vérifier sur https://clarifai.com/api-status
3. Regénérer la clé si nécessaire

### "Erreur 429 Too Many Requests"

**Problème**: Dépassé le quota de 5000 appels/mois

**Solutions**:
1. Attendre le mois suivant
2. Acheter un plan payant
3. Tester en offline avec un modèle local (nécessite TensorFlow)

### "Fichier non trouvé"

**Problème**: `tmp/visual-search/` n'existe pas

**Solutions**:
```bash
mkdir -p tmp/visual-search
chmod 755 tmp/visual-search
```

### "Permission denied on upload"

**Problème**: Impossible d'écrire dans le dossier

**Solutions**:
```bash
# Windows
icacls "tmp/visual-search" /grant Users:F /T

# Linux/Mac
chmod 755 -R tmp/visual-search
```

### "cURL not enabled"

**Problème**: `Call to undefined function curl_init()`

**Solutions**:
1. Éditer `php.ini` (trouvé dans `xampp/php/`)
2. Décommenter `extension=curl`
3. Redémarrer Apache

```ini
extension=curl
extension=gd
```

### "Image trop grande / pas assez grande"

**Problème**: Dimensions d'image invalides

**Solutions**:
- Minimum: 100x100 px
- Maximum: 4000x4000 px
- Format: JPG, PNG, WebP
- Compresser avec un outil en ligne si nécessaire

### "Aucun produit trouvé"

**Problème**: Recherche retourne 0 résultats

**Raisons possibles**:
1. Aucun produit dans la DB
2. Les tags ne matchent pas les noms/descriptions
3. Tous les produits ont stock = 0
4. Image trop ambigüe

**Solutions**:
1. Ajouter des produits d'exemple
2. Tagger les produits avec des noms clairs
3. Vérifier le stock
4. Baisser le seuil `min_confidence` dans la config

### "Erreur réseau / timeout"

**Problème**: Fetch échoue ou timeout

**Solutions**:
1. Vérifier la connexion Internet
2. Vérifier que le serveur Apache tourne
3. Vérifier les logs Apache: `xampp/apache/logs/error.log`
4. Augmenter le timeout PHP dans `php.ini`:
   ```ini
   max_execution_time = 60
   ```

---

## 🔒 Sécurité

### Validations implémentées

✅ **Validation d'extension**
- Uniquement JPG, PNG, WebP acceptés
- Vérification de l'extension du fichier

✅ **Validation MIME type**
- Utilise `finfo_file()` pour vérifier le MIME type réel
- Pas basé sur l'extension du fichier

✅ **Validation de taille**
- Maximum 5MB par défaut
- Configurable dans `config/visual-search.php`

✅ **Validation des dimensions**
- Minimum 100x100 px
- Maximum 4000x4000 px

✅ **Nettoyage des requêtes SQL**
- Utilise `PDO::prepare()` et `execute()`
- Évite les injections SQL

✅ **Noms de fichiers uniques**
- Utilise MD5(timestamp + random)
- Impossible de deviner les chemins

### Recommandations supplémentaires

Pour un projet de production, ajouter:

```php
// Rate limiting
session_start();
if (empty($_SESSION['upload_count'])) {
    $_SESSION['upload_count'] = 0;
    $_SESSION['upload_time'] = time();
}

// Max 10 uploads par 5 minutes
if (time() - $_SESSION['upload_time'] < 300 && $_SESSION['upload_count'] > 10) {
    die('Trop de requêtes');
}

// Scan antivirus (Clamav)
// Intégration des paiements
// Logs des actions
// Monitoring
```

---

## 📊 Quotas Clarifai

| Plan | Appels/mois | Prix |
|------|-----------|------|
| **Free** | 5,000 | $0 |
| **Starter** | 50,000 | $5 |
| **Pro** | 500,000 | $50 |
| **Enterprise** | Illimité | Sur devis |

**Calcul consommation**:
- 1 image = 1 appel à l'API
- 5000 images/mois en gratuit
- ~166 images/jour en gratuit

---

## 🎓 Exemples

### Exemple d'utilisation en PHP

```php
require_once 'config/visual-search.php';
require_once 'app/core/VisualSearch.php';
require_once 'app/core/ImageUpload.php';

$config = require 'config/visual-search.php';
$visualSearch = new VisualSearch($pdo, $config);

// Uploader l'image
$imageUpload = new ImageUpload($config);
$imagePath = $imageUpload->upload($_FILES['image']);

// Analyser et chercher
$result = $visualSearch->search($imagePath);

// Résultat
echo json_encode($result);
```

### Exemple d'utilisation en JavaScript

```javascript
const formData = new FormData();
formData.append('image', fileInput.files[0]);

const response = await fetch('/projet_php/e-store/public/api/visual-search.php', {
    method: 'POST',
    body: formData
});

const data = await response.json();
console.log(data.tags);      // Tags détectés
console.log(data.products);  // Produits trouvés
```

---

## 📚 Ressources

- **Clarifai Docs**: https://docs.clarifai.com/
- **Clarifai API**: https://clarifai.com/api
- **MDN File Upload**: https://developer.mozilla.org/en-US/docs/Web/API/File/File
- **PHP cURL**: https://www.php.net/manual/en/book.curl.php
- **MySQL LIKE**: https://dev.mysql.com/doc/refman/8.0/en/pattern-matching.html

---

## ✨ Améliorations futures

- [ ] Cache des résultats (Redis)
- [ ] Historique des recherches
- [ ] Feedback utilisateur (aide l'IA)
- [ ] Autres API (Google Vision, AWS Rekognition)
- [ ] Export PDF/CSV des résultats
- [ ] Recherche par similarité (embeddings)
- [ ] Mobile app avec React Native
- [ ] Machine learning custom

---

**Besoin d'aide?** Contacter le support ou ouvrir une issue GitHub.

Bon développement! 🚀
