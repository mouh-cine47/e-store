# 🎯 INSTALLATION COMPLÈTE - Visual Search

> Guide complet pour mettre en place Visual Search dans votre E-Store

---

## 📋 Checklist d'installation

Suivez cette checklist pour vous assurer que tout est en place:

### ✅ Phase 1: Fichiers créés

- [ ] `config/visual-search.php` - Configuration générale
- [ ] `app/core/ImageUpload.php` - Classe upload image
- [ ] `app/core/VisualSearch.php` - Classe recherche IA
- [ ] `public/visual-search.php` - Page frontend
- [ ] `public/api/visual-search.php` - API backend
- [ ] `.env.visual-search.example` - Template variables
- [ ] `setup/visual-search-samples.sql` - Données exemple

### ✅ Phase 2: Dossiers créés

- [ ] `tmp/visual-search/` - Uploads temporaires
- [ ] `public/uploads/visual-search/` - Images persistantes
- [ ] `public/api/` - Endpoints API
- [ ] `docs/` - Documentation

### ✅ Phase 3: Configuration

- [ ] `.env` créé (copié depuis `.env.visual-search.example`)
- [ ] `CLARIFAI_API_KEY` rempli dans `.env`
- [ ] Compte Clarifai créé
- [ ] API key Clarifai généré

### ✅ Phase 4: Base de données

- [ ] `setup/visual-search-samples.sql` exécuté
- [ ] 15+ produits d'exemple dans la DB
- [ ] Table `products` avec colonnes: name, description, category

### ✅ Phase 5: Permissions

- [ ] `tmp/visual-search/` readable/writable
- [ ] `public/uploads/visual-search/` readable/writable
- [ ] `config/` readable

### ✅ Phase 6: Extensions PHP

- [ ] PHP 7.4+ installé
- [ ] Extension cURL activée
- [ ] Extension GD activée (pour vérifier images)
- [ ] Extension PDO activée

### ✅ Phase 7: Test

- [ ] Page accessible: http://localhost/projet_php/e-store/public/visual-search.php
- [ ] Upload image fonctionne
- [ ] Analyse IA fonctionne (2-5 secondes)
- [ ] Résultats affichés

---

## 🎬 Commencer (étapes rapides)

### Étape 1: Créer les dossiers

```bash
cd c:\xampp\htdocs\projet_php\e-store

mkdir tmp\visual-search
mkdir public\uploads\visual-search
mkdir public\api
```

### Étape 2: Configuration

```bash
# Copier le template
copy .env.visual-search.example .env

# Éditer .env avec votre clé Clarifai
# CLARIFAI_API_KEY=votre_clé_ici
```

### Étape 3: Produits d'exemple

Via MySQL/phpMyAdmin:
```bash
# Option 1: Ligne de commande
mysql -u root < setup\visual-search-samples.sql

# Option 2: phpMyAdmin
# - Ouvrir http://localhost/phpmyadmin
# - Aller à inventory_db
# - Onglet "Import"
# - Choisir setup/visual-search-samples.sql
# - Cliquer "Import"
```

### Étape 4: Test

```
http://localhost/projet_php/e-store/public/visual-search.php
```

**Résultat attendu**: 
- Page se charge avec zone d'upload
- Upload image fonctionne
- Analyse prend 2-5 secondes
- Résultats affichent tags + produits

---

## 🔑 Obtenir clé API Clarifai

### 1. Inscription

Aller sur: https://clarifai.com/signup

Remplir:
- Email
- Mot de passe
- Confirmer l'email

### 2. Créer application

1. Aller: https://clarifai.com/applications
2. Bouton: "Create New Application"
3. Nom: `e-store-visual-search`
4. Créer

### 3. Copier la clé

1. Aller: https://clarifai.com/settings/applications
2. Sélectionner votre app
3. Onglet: "Keys"
4. Copier: "Personal Access Token"

### 4. Mettre dans .env

Fichier `.env` à la racine:
```env
CLARIFAI_API_KEY=abc123xyz_votre_clé_complète
CLARIFAI_API_URL=https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs
```

### 5. Redémarrer Apache

XAMPP Control Panel:
- Stop Apache
- Start Apache

---

## 📂 Structure finale

Après installation, vous avez:

```
e-store/
│
├─ 📄 config/
│  └─ visual-search.php        (Configuration générale)
│
├─ 📄 app/core/
│  ├─ ImageUpload.php          (Classe upload + validation)
│  ├─ VisualSearch.php         (Classe recherche IA)
│  ├─ Database.php             (Existant)
│  └─ Email.php                (Existant)
│
├─ 📄 public/
│  ├─ visual-search.php        (Page frontend - ACCUEIL)
│  ├─ api/
│  │  └─ visual-search.php     (API backend - AJAX)
│  ├─ uploads/
│  │  ├─ visual-search/        (Images)
│  │  └─ products/             (Existant)
│  └─ ... (autres pages)
│
├─ 📄 tmp/
│  └─ visual-search/           (Images temporaires)
│
├─ 📄 docs/
│  ├─ VISUAL_SEARCH_README.md       (Guide utilisateur)
│  ├─ VISUAL_SEARCH_SETUP.md        (Installation complète)
│  ├─ VISUAL_SEARCH_QUICKSTART.md   (Démarrage rapide)
│  └─ ... (autres docs)
│
├─ 📄 setup/
│  ├─ visual-search-samples.sql (Produits d'exemple)
│  └─ ... (autres fichiers)
│
├─ .env                        (Configuration privée)
├─ .env.visual-search.example  (Template)
└─ ... (autres fichiers)
```

---

## 🚀 Premier lancement

### 1. Vérifier Apache est lancé

XAMPP Control Panel:
- Apache: ✅ Running
- MySQL: ✅ Running

### 2. Ouvrir la page

```
http://localhost/projet_php/e-store/public/visual-search.php
```

### 3. Tester

1. Uploader une image (t-shirt, chaussure, etc.)
2. Attendre l'analyse (2-5 secondes)
3. Voir les tags détectés
4. Voir les produits trouvés
5. Cliquer "Voir" ou "Ajouter panier"

---

## 🔒 Sécurité & Bonnes pratiques

### ✅ Protections implémentées

- Validation d'extension (JPG, PNG, WebP uniquement)
- Validation MIME type
- Validation dimensions (100x100 à 4000x4000)
- Validation taille (max 5MB)
- Requêtes SQL protégées (PDO prepared statements)
- Noms de fichiers uniques (MD5)
- Suppression automatique des fichiers temporaires

### ⚠️ Production (supplémentaires)

Pour un vrai site, ajouter:

```php
// Rate limiting
if ($upload_count > 10 && time() - $upload_time < 300) {
    die('Trop de requêtes');
}

// Scan antivirus
exec('clamscan ' . $filePath);

// Logs
error_log("Visual Search: User {$user_id} searched with image {$imagePath}");

// Monitoring
$metrics = ['uploads' => 100, 'searches' => 95, 'products_found' => 1200];
```

### 📋 .gitignore

Assurez-vous que `.gitignore` contient:

```
.env
tmp/visual-search/*
public/uploads/visual-search/*
*.log
```

**Ne PAS committer**:
- `.env` (contient clé API)
- Images temporaires
- Logs

---

## 📊 Performance

### Temps de réponse attendu

| Étape | Temps |
|-------|-------|
| Upload | 1-2 sec |
| Validation | <1 sec |
| API Clarifai | 2-4 sec |
| Recherche MySQL | <1 sec |
| Affichage | <1 sec |
| **Total** | **4-8 sec** |

### Optimisations

Pour accélérer:

1. **Cache**: Stocker les résultats récents
2. **CDN**: Pour images
3. **Compression**: Gzip responses
4. **Database**: Index sur name/description

---

## 🧪 Tester chaque partie

### Test 1: Upload image

```
1. Ouvrir visual-search.php
2. Cliquer zone upload
3. Choisir image JPG/PNG/WebP
4. Vérifier preview s'affiche
```

✅ **Succès**: Image affichée en preview

### Test 2: API Clarifai

```
1. Uploader image
2. Cliquer "Rechercher"
3. Attendre 2-5 secondes
4. Vérifier tags apparaissent
```

✅ **Succès**: Tags détectés affichés (ex: "shirt", "red", "fabric")

### Test 3: Recherche MySQL

```
1. Analyser une image (ex: chemise rouge)
2. Vérifier produits s'affichent
3. Cliquer "Voir" sur un produit
```

✅ **Succès**: Page produit s'ouvre

### Test 4: Ajouter au panier

```
1. Voir résultats
2. Cliquer icône 🛒 (ajouter panier)
3. Vérifier panier est mis à jour
```

✅ **Succès**: Produit dans le panier

---

## 🐛 Dépannage courant

### "Clé API non trouvée"

**Problème**: Erreur "API key not configured"

**Solutions**:
1. Vérifier `.env` existe à la racine
2. Vérifier `CLARIFAI_API_KEY=...` rempli
3. Pas d'espaces avant/après
4. Redémarrer Apache

```bash
# Vérifier
cat .env
# Devrait afficher: CLARIFAI_API_KEY=abc123...
```

### "Dossier non trouvé"

**Problème**: "Directory not found: tmp/visual-search/"

**Solutions**:
```bash
mkdir tmp\visual-search
mkdir public\uploads\visual-search
```

### "Permission denied"

**Problème**: Erreur d'écriture dans dossiers

**Solutions** (Windows):
```bash
icacls "tmp\visual-search" /grant Users:F /T
icacls "public\uploads\visual-search" /grant Users:F /T
```

### "cURL not enabled"

**Problème**: "Call to undefined function curl_init()"

**Solutions**:
1. Ouvrir `xampp/php/php.ini`
2. Trouver: `;extension=curl`
3. Enlever le `;`: `extension=curl`
4. Redémarrer Apache

### "Aucun produit trouvé"

**Problème**: Zéro résultats même avec produits

**Solutions**:
1. Vérifier produits dans DB: `SELECT * FROM products;`
2. Exécuter: `setup/visual-search-samples.sql`
3. Vérifier stock > 0
4. Essayer une autre image

---

## 📈 Améliorer le système

### Phase 2 (optionnel):

```
1. Cacher les résultats
2. Historique des recherches
3. Feedback utilisateur
4. Autres API (Google Vision)
5. Recherche par texte aussi
6. Mobile app
7. Machine learning custom
```

---

## 🎓 Explication pour l'université

### Projet: AI Visual Search

**Objectif**: 
Permettre aux utilisateurs de chercher des produits via images en utilisant l'IA Clarifai.

**Technologies**:
- Frontend: HTML, CSS, JavaScript (AJAX)
- Backend: PHP 8, MySQL
- IA: Clarifai API (reconnaissance d'objets)
- Design: Bootstrap 5

**Fonctionnalités**:
1. Upload sécurisé d'images
2. Analyse IA des objets
3. Recherche SQL flexible
4. Interface responsive
5. UX moderne avec animations

**Sécurité**:
- Validation stricte des fichiers
- Requêtes SQL protégées
- Gestion des permissions
- Nettoyage des fichiers temporaires

**Points d'apprentissage**:
- API Integration (Clarifai)
- AJAX/Fetch API
- File Upload Security
- Database Queries (LIKE)
- OOP en PHP
- Responsive Web Design

**Résultat**: 
Application complète de recherche visuelle intégrée à l'e-commerce.

---

## 🎯 Prochaines étapes

### 1. Tester complètement
- [ ] Tester upload
- [ ] Tester analyse
- [ ] Tester résultats
- [ ] Tester panier

### 2. Customiser
- [ ] Ajouter au menu principal
- [ ] Changer couleurs
- [ ] Ajouter plus de produits
- [ ] Tester sur mobile

### 3. Déployer
- [ ] Sur server (Heroku, AWS)
- [ ] Avec domain custom
- [ ] Avec SSL/HTTPS
- [ ] Avec monitoring

### 4. Améliorer
- [ ] Cache
- [ ] Historique
- [ ] Feedback
- [ ] Autres API

---

## 📚 Documentation

| Document | Contenu |
|----------|---------|
| [VISUAL_SEARCH_README.md](VISUAL_SEARCH_README.md) | 👥 Guide utilisateur |
| [VISUAL_SEARCH_SETUP.md](VISUAL_SEARCH_SETUP.md) | 🔧 Installation complète |
| [VISUAL_SEARCH_QUICKSTART.md](VISUAL_SEARCH_QUICKSTART.md) | ⚡ Démarrage rapide (5min) |
| Ce document | 🎯 Installation & checklist |

---

## 🤝 Support

Questions?
1. Lire la documentation
2. Vérifier dépannage
3. Chercher logs
4. Contacter support

---

**Installation complète! ✅**

Accéder à: **http://localhost/projet_php/e-store/public/visual-search.php**

Bon développement! 🚀
