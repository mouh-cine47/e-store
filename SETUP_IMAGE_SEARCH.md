# 🔍 Guide Complet : Recherche par Image avec AI

## 📋 Table des matières
1. Architecture de la solution
2. Installation et configuration
3. Guide pas à pas
4. Test de la fonctionnalité
5. Dépannage

---

## 1. Architecture de la solution

### 🏗️ Comment ça fonctionne ?

```
FLUX DE DONNÉES :

User Upload Image
        ↓
    PHP Backend (api_image_search.php)
        ↓
    Clarifai API (gratuit)
        ↓
    Extraction de tags/keywords
        ↓
    Recherche MySQL (comparaison)
        ↓
    Retour des produits similaires
        ↓
    Affichage dans le navigateur
```

### 📦 Fichiers créés/modifiés

```
e-store/
├── config/
│   └── clarifai.php              ← Configuration API Clarifai
├── app/models/
│   └── ImageSearch.php            ← Classe de logique métier
├── public/
│   ├── search_by_image.php        ← Page frontend (HTML/CSS/JS)
│   ├── api_image_search.php       ← Backend API
│   └── shop.php                   ← Modifié (ajout lien menu)
└── tmp/
    └── uploads/                   ← Stockage images temporaires
```

---

## 2. Installation et configuration

### ⚙️ Étape 1 : Créer un compte Clarifai (GRATUIT)

**Pourquoi Clarifai ?**
- ✅ Gratuit (5000 appels/mois)
- ✅ Pas de carte bancaire requise initialement
- ✅ API simple et rapide
- ✅ Reconnaissance d'objets excellente

**Inscription :**

1. Allez sur : https://clarifai.com/
2. Cliquez sur **"Sign Up"**
3. Remplissez le formulaire (email, mot de passe)
4. Confirmez votre email
5. Connectez-vous

**Obtenir votre API Key :**

1. Accédez à : https://clarifai.com/settings/applications
2. Cliquez sur **"Create New Application"**
3. Donnez un nom : `e-store-search`
4. Cliquez sur **"Create"**
5. Vous verrez votre **Personal Access Token** (PAT)
6. Copiez-le complètement

### ⚙️ Étape 2 : Configurer la clé API

1. Ouvrez le fichier : `config/clarifai.php`
2. Trouvez cette ligne :
   ```php
   define('CLARIFAI_API_KEY', 'REMPLACER_PAR_VOTRE_CLE_API');
   ```
3. Remplacez `REMPLACER_PAR_VOTRE_CLE_API` par votre token Clarifai
4. Exemple :
   ```php
   define('CLARIFAI_API_KEY', 'abc123def456xyz789...');
   ```

### ⚙️ Étape 3 : Mettre à jour la base de données

**IMPORTANT** : Pour que la recherche fonctionne bien, ajoutez un index FULLTEXT à la table products.

Connectez-vous à PhpMyAdmin ou MySQL CLI et exécutez :

```sql
-- Ajouter index FULLTEXT (améliore les recherches)
ALTER TABLE products ADD FULLTEXT INDEX ft_search (name, description, brand);
```

Cela permettra de chercher les mots-clés plus rapidement.

---

## 3. Guide pas à pas

### 🚀 Comment utiliser la fonctionnalité ?

**Pour l'utilisateur (client) :**

1. Se connecter au site (shop.php)
2. Cliquer sur le bouton **"🔍 Search Image"** dans le menu
3. Uploader une image (drag & drop ou clic)
4. Cliquer sur **"🔍 Rechercher"**
5. Voir les tags détectés et les produits similaires
6. Cliquer sur un produit pour voir les détails

**Exemples de tests :**

- Image de chemise rouge → Tags: `red`, `shirt`, `fabric` → Produits: chemises rouges dans la DB
- Image de chaussures → Tags: `shoes`, `footwear`, `leather` → Produits: chaussures dans la DB
- Image de sac → Tags: `bag`, `purse`, `leather`, `brown` → Produits: sacs bruns

---

## 4. Test de la fonctionnalité

### ✅ Test complet du système

**Pré-requis :**
- ✅ XAMPP lancé (Apache + MySQL)
- ✅ Clarifai API Key configurée
- ✅ Au moins 1 produit dans la base de données
- ✅ Fichiers créés correctement

**Test pas à pas :**

1. **Vérifier les fichiers :**
   ```
   - config/clarifai.php (existe)
   - app/models/ImageSearch.php (existe)
   - public/api_image_search.php (existe)
   - public/search_by_image.php (existe)
   - tmp/uploads/ (dossier crée)
   ```

2. **Vérifier la base de données :**
   ```sql
   -- Vérifier les produits
   SELECT COUNT(*) FROM products;
   -- Devrait retourner > 0
   ```

3. **Tester l'upload :**
   - Aller sur `http://localhost/projet_php/e-store/public/search_by_image.php`
   - Upload une image (JPG, PNG, ou WebP)
   - Vous devriez voir un aperçu de l'image

4. **Tester l'API Clarifai :**
   - Cliquer sur "🔍 Rechercher"
   - Vous verrez les tags détectés (ex: `shirt`, `clothing`, `fabric`)
   - Les tags s'affichent sous forme de badges

5. **Tester la recherche :**
   - Les produits similaires devraient s'afficher
   - Chaque produit affiche : image, nom, marque, prix
   - Cliquer sur un produit vous mène à sa page détail

### 🐛 Vérifier les erreurs

**Si vous voyez "Erreur lors du chargement" :**

1. Vérifier que XAMPP fonctionne
2. Vérifier que MySQL fonctionne
3. Vérifier que le dossier `tmp/uploads/` a les bonnes permissions

**Si Clarifai retourne une erreur :**

1. Vérifier votre API Key (bien copié-collé)
2. Vérifier que vous avez dépassé votre quota mensuel (5000 appels)
3. Vérifier votre connexion Internet

**Si aucun produit n'est trouvé :**

1. Ajouter plus de produits à la base de données
2. Vérifier que les noms/descriptions des produits correspondent aux tags
3. Réduire la limite de confiance dans ImageSearch.php (ligne 85 : `if ($concept['value'] > 0.5)`)

---

## 5. Dépannage

### ❌ Problème : "Impossible d'analyser l'image"

**Cause :** L'API Clarifai n'a pas pu traiter l'image

**Solution :**
- Essayez une autre image
- Assurez-vous que l'image est claire (bien éclairée)
- Utilisez JPG ou PNG (format courant)

### ❌ Problème : "Aucun produit trouvé"

**Cause :** Les tags ne correspondent pas aux produits

**Solution :**
1. Vérifier que vous avez des produits dans la DB
2. Ajouter des descriptions plus détaillées aux produits
3. Ajouter des tags dans le champ `brand` ou `color`

### ❌ Problème : "Erreur 500"

**Cause :** Erreur PHP ou base de données

**Solution :**
1. Vérifier le fichier `tmp/uploads/` existe et a les bonnes permissions
2. Vérifier que la classe `ImageSearch.php` est bien incluse
3. Vérifier les logs d'erreur PHP (C:/xampp/apache/logs/)

### ❌ Problème : "429 - Too Many Requests"

**Cause :** Vous avez dépassé votre quota Clarifai (5000 appels/mois)

**Solution :**
- Attendre le mois suivant
- Ou passer à un plan payant Clarifai

---

## 📞 Support

### Pour plus d'aide :

- **Clarifai Docs :** https://docs.clarifai.com/
- **PHP cURL :** https://www.php.net/manual/en/book.curl.php
- **MySQL FULLTEXT :** https://dev.mysql.com/doc/refman/8.0/en/fulltext-search.html

---

## 🎓 Améliorations futures (optionnelles)

Une fois que ça fonctionne, vous pouvez ajouter :

1. **Cache des résultats** - Stocker les résultats en base de données
2. **Historique des recherches** - Garder les images et résultats
3. **Feedback utilisateur** - "Était-ce utile ?" (améliore l'IA)
4. **Autres API** :
   - Google Vision API (gratuit, 1000 appels/mois)
   - Amazon Rekognition (payant, très puissant)

---

## ✨ Résumé des technologies

| Composant | Technologie | Raison |
|-----------|-------------|--------|
| Upload | HTML5 Form | Simple et natif |
| Drag & Drop | JavaScript | Meilleure UX |
| Analyse image | Clarifai API | Gratuit et facile |
| Recherche | MySQL FULLTEXT | Rapide et simple |
| Frontend | Vanilla JS + Bootstrap | Pas de dépendances |
| Backend | PHP natif | Déjà utilisé dans le projet |

---

Bonne chance ! 🚀
