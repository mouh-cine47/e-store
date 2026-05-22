# ⚡ Guide de Démarrage Rapide - Recherche par Image

## 🎯 Objectif
Permettre aux utilisateurs de chercher des produits en uploadant une image.

## ✅ Ce qui a été créé

### Fichiers PHP créés
- `public/search_by_image.php` - Page de recherche (interface utilisateur)
- `public/api_image_search.php` - API avec Clarifai
- `public/api_image_search_simple.php` - API alternative (sans API externe)
- `public/diagnostic_image_search.php` - Page de diagnostic
- `app/models/ImageSearch.php` - Classe Clarifai
- `app/models/ImageSearchSimple.php` - Classe analyse simple
- `config/image_search_config.php` - Configuration centralisée
- `tmp/uploads/` - Dossier pour les images temporaires

### Modifications
- `public/shop.php` - Ajout bouton "🔍 Search Image" dans le menu

---

## 🚀 Démarrer en 5 minutes (méthode simple)

### Étape 1 : Aucune configuration requise !
La version "analyse simple" fonctionne **directement** sans API.

### Étape 2 : Tester
Ouvrez votre navigateur et allez à :
```
http://localhost/projet_php/e-store/public/search_by_image.php
```

### Étape 3 : Upload une image
- Uploadez une photo d'un vêtement (t-shirt, chemise, etc.)
- Cliquez sur "🔍 Rechercher"

### Étape 4 : Voir les résultats
Les produits de couleur similaire devraient s'afficher.

---

## 🤖 Utiliser Clarifai (meilleure reconnaissance)

Si vous voulez une **meilleure reconnaissance** :

### Étape 1 : Créer un compte Clarifai
1. Allez sur https://clarifai.com/
2. Inscrivez-vous (gratuit, pas de CB)
3. Allez sur https://clarifai.com/settings/applications
4. Cliquez "Create New Application"
5. Copiez votre **Personal Access Token**

### Étape 2 : Configurer votre clé
Ouvrez `config/image_search_config.php` et changez :

```php
// Avant :
define('USE_CLARIFAI', false);
define('CLARIFAI_API_KEY', 'VOTRE_CLE_CLARIFAI_ICI');

// Après :
define('USE_CLARIFAI', true);
define('CLARIFAI_API_KEY', 'votre_vraie_cle_ici');
```

### Étape 3 : Tester
Revenez à la page de recherche et testez.

---

## 🔍 Tester votre configuration

Ouvrez la page de diagnostic :
```
http://localhost/projet_php/e-store/public/diagnostic_image_search.php
```

Elle vous montre :
- ✅ Quelle méthode est activée
- ✅ Si tout fonctionne
- ⚠️ Les erreurs potentielles
- 📋 Les prochaines étapes

---

## 📊 Comparaison des deux méthodes

### ✨ Analyse Simple (par défaut)
- ✅ Zéro configuration
- ✅ Pas d'API externe
- ✅ Fonctionne offline
- ✅ Gratuit et illimité
- ⚠️ Basé sur les couleurs (moins précis)

**Utilisation :** `config/image_search_config.php`
```php
define('USE_CLARIFAI', false);
define('USE_SIMPLE_ANALYSIS', true);
```

### 🚀 Clarifai AI
- ✅ Reconnaissance d'objets très précise
- ✅ Détecte types de produits (shirt, shoes, etc.)
- ✅ Gratuit (5000 appels/mois)
- ⚠️ Nécessite inscription
- ⚠️ Nécessite clé API
- ⚠️ Internet requis

**Utilisation :** `config/image_search_config.php`
```php
define('USE_CLARIFAI', true);
define('CLARIFAI_API_KEY', 'votre_cle');
```

---

## 💡 Comment ça fonctionne

### Flux complet :

```
1. USER : Upload une image
           ↓
2. FRONTEND : Affiche aperçu + bouton "Chercher"
           ↓
3. BACKEND : Appelle API_IMAGE_SEARCH.PHP
           ↓
4. ANALYSIS :
   - Si Clarifai : Envoie à l'API cloud
   - Si Simple : Analyse locale (couleurs)
           ↓
5. TAGS : Récupère mots-clés
           (ex: "red", "shirt", "cotton")
           ↓
6. DATABASE : Cherche produits correspondants
           (WHERE color='red' AND ...)
           ↓
7. RESULTS : Affiche produits trouvés
           ↓
8. USER : Clique et achète 😎
```

---

## 📱 Comment ça s'affiche

**Pour l'utilisateur :**
1. Page simple avec zone d'upload
2. Aperçu de l'image
3. Tags détectés (ex: red, blue, pattern)
4. Grille de produits similaires
5. Chaque produit : image, nom, marque, prix

---

## 🛠️ Personnaliser

### Changez le nombre de résultats
Ouvrez `config/image_search_config.php` :
```php
define('MAX_PRODUCTS', 12);  // ← Changez 12 par autre nombre
```

### Changez le score minimum de confiance Clarifai
```php
define('CLARIFAI_MIN_CONFIDENCE', 0.5);  // ← 0.5 = 50%
```

### Changez la taille max de fichier
```php
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // ← 5MB
```

---

## ❓ FAQ

### "Ça ne trouve pas de produits"
- Vérifiez que vous avez des produits dans la DB (avec stock > 0)
- Essayez une image plus claire
- Pour Clarifai : attendez quelques secondes (API cloud)

### "Erreur API Clarifai"
- Vérifiez votre clé API (bien copiée ?)
- Vérifiez votre quota (5000/mois)
- Vérifiez votre connexion Internet

### "La page ne charge pas"
- Vérifiez que XAMPP fonctionne
- Vérifiez que MySQL fonctionne
- Vérifiez les logs d'erreur PHP

### "Comment ajouter d'autres API ?"
Vous pouvez créer votre propre classe dans `app/models/` et l'utiliser à la place.

---

## 📚 Ressources

- **Clarifai Docs** : https://docs.clarifai.com/
- **PHP GD** : https://www.php.net/manual/en/book.gd.php
- **PHP cURL** : https://www.php.net/manual/en/book.curl.php
- **MySQL FULLTEXT** : https://dev.mysql.com/doc/refman/8.0/en/fulltext-search.html

---

## ✨ Prochaines améliorations (optionnelles)

1. **Cache** : Mémoriser les résultats pour les mêmes images
2. **Historique** : Garder l'historique des recherches
3. **Feedback** : "Était-ce utile ?" pour améliorer
4. **Autres modèles** : Utiliser d'autres IA (Google Vision, AWS Rekognition)
5. **Export** : Exporter les résultats en PDF/CSV

---

## 🎓 Architecture propre

```
e-store/
├── config/
│   ├── pdo.php                    ← Connexion DB
│   ├── image_search_config.php    ← NOUVEAU : Configuration
│   └── clarifai.php               ← Ancienne config (déprécié)
│
├── app/models/
│   ├── ImageSearch.php            ← NOUVEAU : Clarifai
│   └── ImageSearchSimple.php      ← NOUVEAU : Analyse simple
│
├── public/
│   ├── search_by_image.php        ← NOUVEAU : Interface
│   ├── api_image_search.php       ← NOUVEAU : API Clarifai
│   ├── api_image_search_simple.php ← NOUVEAU : API Simple
│   ├── diagnostic_image_search.php ← NOUVEAU : Diagnostic
│   └── shop.php                   ← MODIFIÉ : Ajout lien
│
└── tmp/
    └── uploads/                   ← NOUVEAU : Images temp
```

---

**Vous êtes prêt ! 🚀**

Allez sur : `http://localhost/projet_php/e-store/public/search_by_image.php`

Amusez-vous ! 😊
