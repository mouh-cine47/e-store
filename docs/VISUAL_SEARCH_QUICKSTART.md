# 🚀 DÉMARRAGE RAPIDE - Visual Search (5 min)

Vous êtes débutant? Suivez ce guide étape par étape!

---

## ✅ Prérequis (vérifier avant de commencer)

### 1. XAMPP est installé et lancé?

```
Apache: ✅ Running (port 80)
MySQL:  ✅ Running (port 3306)
```

Si non:
1. Ouvrir XAMPP Control Panel
2. Cliquer sur "Start" pour Apache et MySQL

### 2. PHP version 7.4+?

```bash
php -v
```

Devrait afficher: `PHP 7.4.0` ou plus récent.

### 3. Extension cURL activée?

```bash
php -m | findstr curl
```

Si rien n'apparaît, éditer `php.ini`:
1. Chercher: `extension=curl`
2. Décommenter (enlever le `;`)
3. Redémarrer Apache

---

## 📋 Installation (5 minutes)

### Étape 1: Créer les dossiers

Depuis la racine du projet (`c:\xampp\htdocs\projet_php\e-store\`):

```bash
mkdir tmp\visual-search
mkdir public\uploads\visual-search
mkdir public\api
```

### Étape 2: Copier la configuration

```bash
# Depuis la racine
copy .env.visual-search.example .env
```

Éditer le fichier `.env`:
```env
CLARIFAI_API_KEY=votre_clé_ici
```

### Étape 3: Ajouter des produits d'exemple

```bash
# Depuis MySQL/phpMyAdmin
mysql -u root < setup/visual-search-samples.sql
```

Ou avec phpMyAdmin:
1. Ouvrir: http://localhost/phpmyadmin
2. Sélectionner base `inventory_db`
3. Onglet "Import"
4. Choisir fichier: `setup/visual-search-samples.sql`
5. Cliquer "Import"

### Étape 4: Tester!

Ouvrir: **http://localhost/projet_php/e-store/public/visual-search.php**

---

## 🔑 Obtenir la clé API Clarifai (3 minutes)

### 1. Créer un compte

Aller sur: https://clarifai.com
- Cliquer "Sign Up"
- Remplir le formulaire
- Confirmer l'email

### 2. Créer une application

1. Aller à: https://clarifai.com/applications
2. Cliquer "Create New Application"
3. Nommer: `e-store-visual-search`
4. Cliquer "Create Application"

### 3. Copier la clé API

1. Aller à: https://clarifai.com/settings/applications
2. Sélectionner votre app
3. Onglet "Keys"
4. Copier le "Personal Access Token"
5. Coller dans le fichier `.env`:

```env
CLARIFAI_API_KEY=abc123xyz_votreClé
```

### 4. Redémarrer Apache

- XAMPP Control Panel → Stop Apache → Start Apache

---

## 🎯 Premiers pas

### 1. Ouvrir la page Visual Search

```
http://localhost/projet_php/e-store/public/visual-search.php
```

### 2. Uploader une image

- Cliquer sur la zone grise
- Choisir une image (JPG, PNG, WebP)
- Maximum 5MB

### 3. Attendre l'analyse

- Spinner affiche "Analyse en cours..."
- 2-5 secondes normalement

### 4. Voir les résultats

- Tags détectés en haut
- Produits similaires en bas
- Cliquer "Voir" pour détails

---

## 🧪 Test rapide

### Image pour tester:

Télécharger une image d'exemple:
1. Chercher sur Google: "red shirt", "blue jeans", "sneakers"
2. Télécharger une image
3. Uploader sur Visual Search

### Ou dessiner:

- Utiliser Paint pour dessiner un vêtement simple
- Sauvegarder en JPG
- Uploader

Résultat attendu: Tags "shirt", "fabric", "clothing" etc.

---

## 📂 Structure créée

Vous avez maintenant:

```
e-store/
├── config/
│   └── visual-search.php          ✅
├── app/core/
│   ├── ImageUpload.php            ✅
│   └── VisualSearch.php           ✅
├── public/
│   ├── visual-search.php          ✅
│   ├── api/
│   │   └── visual-search.php      ✅
│   └── uploads/
│       └── visual-search/         ✅
├── tmp/
│   └── visual-search/             ✅
├── setup/
│   └── visual-search-samples.sql  ✅
└── .env                           ✅
```

---

## ❌ Troubleshooting rapide

### Erreur: "API key not configured"
→ Vérifier `.env` a la bonne clé
→ Redémarrer Apache

### Erreur: "File not found"
→ Vérifier que `tmp/visual-search/` existe
→ Créer: `mkdir tmp\visual-search`

### Erreur: "Permission denied"
→ Dossiers doivent avoir permissions 755
→ Windows: généralement ok automatiquement

### Aucun produit trouvé
→ Vérifier produits dans la DB
→ Exécuter: `setup/visual-search-samples.sql`

### Analyseur long (>5s)
→ Vérifier connexion Internet
→ Tester: https://clarifai.com/api-status

---

## 📚 Étapes suivantes

1. **Lire la documentation compète**: 
   → [VISUAL_SEARCH_SETUP.md](VISUAL_SEARCH_SETUP.md)

2. **Guide utilisateur**:
   → [VISUAL_SEARCH_README.md](VISUAL_SEARCH_README.md)

3. **Ajouter à la navbar**:
   → Éditer `includes/header.php`
   → Ajouter lien "Visual Search"

4. **Ajouter à l'accueil**:
   → Éditer `public/home.php`
   → Ajouter bouton "Essayer Visual Search"

---

## ✨ Fonctionnalités avancées (optionnel)

### Ajouter au menu principal

Éditer `includes/header.php`:

```html
<li class="nav-item">
    <a class="nav-link" href="/projet_php/e-store/public/visual-search.php">
        <i class="fas fa-search"></i> Visual Search
    </a>
</li>
```

### Ajouter bouton sur la page d'accueil

Éditer `public/home.php`, ajouter dans le hero:

```html
<a href="visual-search.php" class="btn btn-primary">
    🔍 Rechercher par image
</a>
```

### Customiser les couleurs

Éditer `public/visual-search.php`, chercher:

```javascript
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

Et remplacer par vos couleurs.

---

## 🎓 Concepts clés

### Ce qu'il se passe:

```
1. VOUS uploadez une image
        ↓
2. JAVASCRIPT valide et envoie
        ↓
3. PHP reçoit et valide
        ↓
4. IMAGE est sauvegardée temporairement
        ↓
5. ENVOYÉE à l'API Clarifai
        ↓
6. L'IA reconnaît les objets → retourne TAGS
        ↓
7. TAGS utilisés pour chercher dans MySQL
        ↓
8. PRODUITS trouvés retournés
        ↓
9. RÉSULTATS affichés joliment
        ↓
10. IMAGE supprimée (nettoyage)
```

### Sécurité:

- ✅ Validation extension + taille
- ✅ Requêtes SQL protégées (PDO)
- ✅ Noms uniques (MD5)
- ✅ Images temporaires supprimées

---

## 💡 Conseils

✅ **Première fois**:
- Prendre votre temps
- Lire chaque étape
- Tester après chaque étape

✅ **Problème**:
- Vérifier les logs: `xampp/apache/logs/error.log`
- Redémarrer Apache
- Vider le cache navigateur

✅ **Produits**:
- Plus de produits = meilleurs résultats
- Noms clairs (pas "Item 123")
- Descriptions détaillées

✅ **Images**:
- Bien éclairées
- Nettes et claires
- Produit visible

---

## 📞 Support

Questions?
- Lire: [VISUAL_SEARCH_SETUP.md](VISUAL_SEARCH_SETUP.md#dépannage)
- GitHub Issues
- Contacter support

---

**C'est bon! 🎉**

Vous êtes prêt à utiliser Visual Search!

Accéder à: http://localhost/projet_php/e-store/public/visual-search.php

Bon shopping! 🛍️
