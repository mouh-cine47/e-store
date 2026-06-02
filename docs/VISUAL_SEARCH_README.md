# 🔍 Visual Search - Guide Utilisateur

> Trouvez des produits similaires en uploadant une image!

## 🎯 Qu'est-ce que Visual Search?

Visual Search est une fonctionnalité qui vous permet de:

1. **Uploader une image** d'un produit qui vous intéresse
2. **L'IA analyse l'image** et détecte les objets/concepts
3. **Reçoit une liste de produits similaires** directement!

Parfait pour:
- Trouver un article similaire à celui que vous avez vu
- Découvrir des alternatives moins chères
- Chercher des variations (couleurs, marques différentes)

---

## 🚀 Comment utiliser?

### Étape 1: Accéder à Visual Search

Allez à la page:
```
http://localhost/projet_php/e-store/public/visual-search.php
```

Ou cliquez sur le lien "Visual Search" dans le menu.

### Étape 2: Uploader une image

Vous pouvez:

**Option A: Cliquer sur la zone**
- Cliquer sur la zone grise
- Choisir une image JPG, PNG ou WebP
- Maximum 5MB

**Option B: Glisser-déposer**
- Glisser une image depuis votre ordinateur
- Déposer sur la zone grise
- C'est tout!

### Étape 3: Vérifier l'aperçu

- L'image s'affiche en aperçu
- Vous voyez le nom et la taille du fichier
- Si l'image ne plaît pas, en choisir une autre

### Étape 4: Cliquer "Rechercher"

- Attendre 2-5 secondes (dépend de la connexion)
- Un spinner indique que l'IA analyse l'image
- Pas de rechargement de page (technologie AJAX)

### Étape 5: Voir les résultats

**Tags détectés** (en haut)
- Affiche les objets reconnus par l'IA
- Exemple: "shirt", "red", "fabric"
- Chaque tag a un % de confiance

**Produits similaires** (en bas)
- Grille de produits trouvés
- Image, nom, prix, catégorie
- Boutons:
  - **"Voir"** → Détails du produit
  - **"🛒"** → Ajouter au panier

---

## 💡 Conseils d'utilisation

### Pour de meilleurs résultats:

✅ **Bonne image**
- Bien éclairée (bonne lumière)
- Nette et claire
- Produit bien visible
- Pas trop zoomer ni dézoomer

❌ **Mauvaise image**
- Flou ou pixelisée
- Trop sombre
- Trop petite (moins de 100x100 px)
- Trop grande (plus de 4000x4000 px)

✅ **Formats autorisés**
- JPEG / JPG
- PNG
- WebP

❌ **Formats NON autorisés**
- GIF
- BMP
- TIFF
- PDF (n'importe quel autre)

### Exemples de recherches

| Recherche | Attendu |
|-----------|---------|
| Photo d'une chemise rouge | Toutes les chemises, tous les articles rouges |
| Photo de baskets Nike | Baskets, chaussures de sport |
| Photo d'un sac à main | Sacs, accessoires |
| Photo d'un pantalon bleu | Pantalons, articles bleus |

---

## ❓ Questions fréquentes

### Q: Peut-on uploader plusieurs images?
**R**: Une par une. Pour chaque nouvelle recherche, uploader une nouvelle image.

### Q: Combien de temps ça prend?
**R**: 
- Analyse IA: 2-5 secondes
- Dépend de la connexion Internet
- Plus rapide avec une bonne connexion

### Q: Aucun produit trouvé, pourquoi?
**R**: Possible raisons:
1. L'IA n'a pas reconnu les objets
2. Aucun produit similaire en stock
3. Image trop ambigüe ou peu claire
4. Essayer une autre image!

### Q: L'IA s'est trompée?
**R**: C'est normal! L'IA n'est pas parfaite. Essayer:
1. Une meilleure photo
2. Plus proche du produit
3. Avec meilleure lumière

### Q: Mes données sont sauvegardées?
**R**: 
- Les images sont supprimées automatiquement après la recherche
- Aucun stockage permanent
- Respecte votre vie privée

### Q: Puis-je utiliser n'importe quelle image?
**R**: Oui! Mais pour des résultats:
- Utiliser des photos de produits
- Pas de screenshots
- Pas de dessins
- Pas de captures d'écran

### Q: Est-ce gratuit?
**R**: Oui! Mais:
- Limité à 5000 recherches/mois
- ~166 recherches/jour en moyenne
- Suffisant pour un usage personnel

### Q: Peut-on exporter les résultats?
**R**: Actuellement non, mais bientôt:
- Télécharger en PDF
- Exporter en CSV
- Partager sur réseaux sociaux

---

## 🎓 Comment ça marche? (Technique)

Pour les curieux! 

### Les 5 étapes

```
1. UPLOAD
   Vous uploadez une image JPG/PNG/WebP
   Vérification: taille, format, dimensions

2. ANALYSE IA (Clarifai)
   L'image est envoyée à l'API Clarifai
   L'IA reconnaît les objets
   Retour: liste des tags + confiance

3. RECHERCHE
   Les tags sont utilisés pour chercher
   dans la base de données produits

4. RÉSULTATS
   Affichage des produits trouvés
   Avec images, prix, catégories

5. NETTOYAGE
   L'image temporaire est supprimée
   Respecte la vie privée
```

### Tags retournés

Exemple pour une chemise rouge:

```
- shirt          95% de confiance
- red            87% de confiance  
- fabric         76% de confiance
- cotton         65% de confiance
- fashion        58% de confiance
```

Le système cherche des produits contenant ces mots.

### Technologie utilisée

- **Frontend**: HTML, CSS, JavaScript (AJAX)
- **Backend**: PHP 8+
- **Base de données**: MySQL
- **IA**: Clarifai API (reconnaissance d'objets)
- **Design**: Bootstrap 5

---

## 🚨 Erreurs possibles

### "Aucune image uploadée"
→ Sélectionner une image avant de cliquer "Rechercher"

### "L'image dépasse 5MB"
→ Compresser l'image avant upload

### "Extension non autorisée"
→ Utiliser JPG, PNG ou WebP

### "Image trop petite"
→ Minimum 100x100 pixels

### "Erreur lors de l'analyse"
→ Vérifier votre connexion Internet

### "Erreur base de données"
→ Contacter le support

### "Aucun produit trouvé"
→ Essayer une autre image
→ Vérifier qu'il y a des produits en stock

---

## 📱 Sur mobile?

Visual Search fonctionne aussi sur téléphone:

1. Ouvrir la page sur votre téléphone
2. Uploader une photo depuis votre galerie
3. Ou prendre une nouvelle photo en direct
4. Tout fonctionne pareil!

Interface responsive et adaptée au mobile.

---

## 🔐 Sécurité & Vie privée

✅ **Votre image n'est pas stockée**
- Analysée directement
- Supprimée après utilisation
- Aucun historique sauvegardé

✅ **Vos données sont sûres**
- Connexion sécurisée (HTTPS)
- Pas de cookies de tracking
- Respect du RGPD

---

## 🎁 Cas d'usage

### Cas 1: Trouver une alternative

Vous aimez un article mais c'est trop cher?
- Prendre une photo
- Uploader sur Visual Search
- Trouver des alternatives moins chères

### Cas 2: Couleur/marque différente

Vous avez vu un pantalon mais:
- La couleur n'était pas bonne
- Visual Search vous montre toutes les couleurs disponibles

### Cas 3: Style similaire

Vous aimez un style (ex: casual, sport, formel):
- Prendre une photo d'une pièce du style
- Découvrir d'autres pièces du même style

### Cas 4: Complément de garde-robe

Trouver un haut qui va avec votre bas:
- Photo du bas
- Visual Search → Trouver des hauts assortis

---

## 💬 Feedback

Vous avez des suggestions?
- Contacter le support
- Ouvrir une issue sur GitHub
- Proposer des améliorations

---

## 📚 En savoir plus

- **Documentation technique**: [VISUAL_SEARCH_SETUP.md](VISUAL_SEARCH_SETUP.md)
- **Clarifai**: https://clarifai.com/
- **E-Store Docs**: [README.md](README.md)

---

**Amusez-vous! 🛍️**

Besoin d'aide? Consultez la [FAQ](VISUAL_SEARCH_SETUP.md#dépannage).
