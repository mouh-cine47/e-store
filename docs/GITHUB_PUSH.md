# 🚀 Guide : Pousser le projet sur GitHub

## ✅ Prérequis

1. **Git installé** : https://git-scm.com/download/win
2. **Compte GitHub** : https://github.com (gratuit)
3. **SSH ou HTTPS configuré** (SSH recommandé)

---

## 📝 Étape 1 : Créer un repo GitHub (5 min)

### Méthode 1 : Via le site GitHub (le plus simple)

1. Allez sur https://github.com/new
2. Remplissez :
   - **Repository name** : `e-store` (ou autre nom)
   - **Description** : `E-Store - Professional Online Shopping Platform`
   - **Public** ou **Private** : À vous de choisir
   - **Initialize with README** : NON (vous en avez déjà un)
   - **Add .gitignore** : NON (vous en avez déjà un)
   - **License** : MIT (optionnel)
3. Cliquez **"Create repository"**
4. Vous arrivez sur une page vide avec une URL : `https://github.com/VOTRE_PSEUDO/e-store.git`

**Gardez cette URL**, vous en aurez besoin.

### Méthode 2 : Via GitHub CLI (si vous l'avez)

```bash
gh repo create e-store --public --source=. --remote=origin --push
```

---

## 💻 Étape 2 : Pousser depuis Windows (PowerShell)

Ouvrez **PowerShell** dans le dossier du projet :
```powershell
cd C:\xampp\htdocs\projet_php\e-store
```

### Option A : Si le projet n'est PAS encore un repo Git

**Initialiser Git** :
```powershell
git init
```

**Ajouter tous les fichiers** :
```powershell
git add .
```

**Créer le premier commit** :
```powershell
git commit -m "Initial commit: E-Store - Professional online shopping platform"
```

**Ajouter le repo distant (remplacer YOUR_USERNAME et URL)** :
```powershell
git remote add origin https://github.com/VOTRE_PSEUDO/e-store.git
```

**Vérifier le remote** :
```powershell
git remote -v
```
Vous devriez voir :
```
origin  https://github.com/VOTRE_PSEUDO/e-store.git (fetch)
origin  https://github.com/VOTRE_PSEUDO/e-store.git (push)
```

**Pousser sur GitHub** :
```powershell
git branch -M main
git push -u origin main
```

Entrez votre **username GitHub** et **token** (pas mot de passe) si demandé.

### Option B : Si c'est déjà un repo Git

Juste pousser :
```powershell
git remote add origin https://github.com/VOTRE_PSEUDO/e-store.git
git branch -M main
git push -u origin main
```

---

## 🔐 Authentification GitHub (HTTPS)

Si vous utilisez HTTPS et que Git demande un mot de passe :

### Créer un Personal Access Token

1. Allez sur https://github.com/settings/tokens/new
2. Cochez `repo` (accès complet au repo)
3. Générez le token
4. **Copiez-le** (vous ne le reverrez qu'une fois !)
5. Utilisez ce token comme "mot de passe" quand Git demande

### Sauvegarder le token localement

```powershell
# Windows - Credential Manager
git config --global credential.helper wincred
```

Ensuite Git mémorisera automatiquement.

---

## 🔒 Authentification GitHub (SSH) - Recommandé

### 1. Générer une clé SSH

```powershell
ssh-keygen -t ed25519 -C "votre_email@gmail.com"
```

Appuyez sur **Enter** pour tous les prompts (pas de passphrase).

Les clés sont créées dans : `C:\Users\VOTRE_USER\.ssh\`

### 2. Ajouter la clé SSH à GitHub

**Copier la clé publique** :
```powershell
cat C:\Users\VOTRE_USER\.ssh\id_ed25519.pub | clip
```

**Sur GitHub** :
1. Allez sur https://github.com/settings/keys
2. Cliquez **"New SSH key"**
3. Collez (Ctrl+V)
4. Cliquez **"Add SSH key"**

### 3. Utiliser SSH pour pousser

```powershell
git remote set-url origin git@github.com:VOTRE_PSEUDO/e-store.git
git push -u origin main
```

---

## ✅ Vérifier que c'est poussé

**Sur GitHub :**
1. Allez sur votre repo : https://github.com/VOTRE_PSEUDO/e-store
2. Vous devriez voir votre code
3. Vérifiez que `database.sql` N'EST PAS listé (doit être dans .gitignore)
4. Vérifiez que `config/image_search_config.php` N'EST PAS listé (doit être dans .gitignore)

**Via PowerShell** :
```powershell
git log --oneline
```

Vous devriez voir votre commit initial.

---

## 📋 Fichiers à POUSSER ✅

Ces fichiers DOIVENT être sur GitHub :
- ✅ `public/` (toutes les pages)
- ✅ `admin/` (all dashboard pages)
- ✅ `auth/` (authentication)
- ✅ `app/` (application core)
- ✅ `config/` (configuration)
- ✅ `database.sql` (database schema)
- ✅ `.gitignore` (git configuration)
- ✅ `README.md` (documentation)
- ✅ `composer.json` (dependencies)
- ✅ `QUICK_START.md`
- ✅ Tous les autres fichiers du projet

## 📋 Fichiers à IGNORER ❌

Ces fichiers NE doivent PAS être sur GitHub :
- ❌ `tmp/uploads/*` (images temporaires)
- ❌ `vendor/` (dépendances)
- ❌ `.env` (secrets)
- ❌ `*.log` (error logs)

(Vérifiez que `.gitignore` les liste)

---

## 🔄 Après le premier push

Pour les commits suivants :

```powershell
# Vérifier les changements
git status

# Ajouter les fichiers modifiés
git add .

# Ou ajouter un fichier spécifique
git add public/search_by_image.php

# Commit avec un message descriptif
git commit -m "feat: Add new API endpoint for image classification"

# Pousser
git push
```

---

## 🚨 If You Made a Mistake

### You accidentally pushed sensitive files?

**IMPORTANT** : Secrets pushed remain in history!

```powershell
# Option 1 : Add to .gitignore and future commits (recommended)
# Edit .gitignore to exclude sensitive files

# Option 2 : Rewrite history (dangerous, for experts)
# Ask for help if needed!
```

---

## 💡 Bonnes pratiques Git

```powershell
# Message de commit descriptif
git commit -m "feat: Add image search functionality"
git commit -m "fix: Fix database FULLTEXT search query"
git commit -m "docs: Update README with examples"

# Voir l'historique
git log --oneline --graph

# Voir les changements non poussés
git log origin/main..main

# Revert un commit
git revert HASH_DU_COMMIT

# Annuler les modifications locales
git checkout -- public/search_by_image.php
```

---

## 🎯 Résumé des commandes essentielles

```powershell
# 1. Initialiser (première fois)
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/VOTRE_PSEUDO/e-store.git
git branch -M main
git push -u origin main

# 2. Pousser après modifications
git add .
git commit -m "Description du changement"
git push

# 3. Vérifier l'état
git status
git log --oneline
```

---

## 📚 Ressources

- **Git Docs** : https://git-scm.com/doc
- **GitHub Docs** : https://docs.github.com/en
- **GitHub CLI** : https://cli.github.com/

---

**Vous êtes prêt ! 🚀**

Des questions ? Consultez les logs ou ouvrez une **GitHub Issue** sur votre repo.
