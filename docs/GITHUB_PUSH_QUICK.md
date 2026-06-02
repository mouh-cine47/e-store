# 📤 Pousser sur GitHub - Résumé rapide

## 🎯 3 méthodes (du plus simple au plus complexe)

---

## ✨ Méthode 1 : Script automatisé (PLUS SIMPLE - RECOMMANDÉ)

**Windows PowerShell :**

```powershell
# 1. Ouvrir PowerShell dans le dossier du projet
cd C:\xampp\htdocs\projet_php\e-store

# 2. Lancer le script
.\push-to-github.ps1 -GitHubUrl "https://github.com/VOTRE_PSEUDO/e-store.git"
```

Le script fait **tout automatiquement** :
- ✅ Initialise Git
- ✅ Ajoute les fichiers
- ✅ Crée un commit
- ✅ Ajoute le remote GitHub
- ✅ Pousse sur GitHub

**C'est fini ! 🎉**

---

## 🤖 Méthode 2 : Commandes PowerShell manuelles

**Étape 1 : Créer un repo sur GitHub**
- Allez sur https://github.com/new
- Nom : `e-store`
- Cliquez **"Create repository"**
- Notez l'URL (ex: `https://github.com/VOTRE_PSEUDO/e-store.git`)

**Étape 2 : Initialiser et pousser**

```powershell
# Allez dans le dossier
cd C:\xampp\htdocs\projet_php\e-store

# Initialiser Git
git init

# Ajouter tous les fichiers
git add .

# Créer un commit
git commit -m "Initial commit: E-Store - Professional shopping platform"

# Ajouter le remote (remplacez l'URL)
git remote add origin https://github.com/VOTRE_PSEUDO/e-store.git

# Renommer la branche à 'main'
git branch -M main

# Pousser
git push -u origin main
```

**C'est fini ! 🎉**

---

## 🔐 Méthode 3 : GitHub CLI (plus moderne)

**Si vous avez GitHub CLI installé :**

```powershell
# Créer un nouveau repo et pousser en une commande
gh repo create e-store --public --source=. --remote=origin --push
```

**C'est fini ! 🎉**

---

## ❓ FAQ GitHub

### Q : Qu'est-ce qui est poussé sur GitHub ?
**R :** Tous les fichiers SAUF ceux dans `.gitignore` :
- ✅ Poussé : `public/search_by_image.php`, code source, README, etc.
- ❌ Pas poussé : `config/image_search_config.php` (secrets), `tmp/uploads/` (temp)

### Q: What files get pushed to GitHub?
**A:** All files EXCEPT those in `.gitignore`:
- ✅ Pushed: `public/`, `admin/`, `app/`, source code, README, etc.
- ❌ Not pushed: `.env` (secrets), `tmp/uploads/` (temp files)

### Q: How do I secure my secrets?
**A:** 
1. Never commit files with real API keys or passwords
2. Use `.env.example` as a template (without secrets)
3. Other developers copy `.env.example` and rename it to `.env`
4. Add sensitive files to `.gitignore`

### Q: I accidentally pushed a secret file!
**A:** 
1. Add the file to `.gitignore` immediately
2. Make a new commit without the file
3. Ask for help if you want to clean the history

### Q : Quels fichiers du projet vais-je voir sur GitHub ?
**R :**
```
e-store/ (on GitHub)
├── public/           ✅ Visible
├── admin/            ✅ Visible
├── app/              ✅ Visible
├── auth/             ✅ Visible
├── config/           ✅ Visible
├── assets/           ✅ Visible
├── tmp/uploads/      ❌ Hidden (temp)
├── README.md         ✅ Visible
├── QUICK_START.md    ✅ Visible
└── database.sql      ✅ Visible
```

### Q : Comment cloner le projet ailleurs ?
**R :**
```powershell
git clone https://github.com/VOTRE_PSEUDO/e-store.git
cd e-store
# Database setup in README.md
```

### Q : Comment faire un commit supplémentaire après le premier ?
**R :**
```powershell
# Modifier des fichiers...
git add .
git commit -m "Description du changement"
git push
```

---

## 🛠️ Authentification GitHub

### HTTPS (mot de passe / token)

GitHub n'accepte PLUS les mots de passe. Utilisez un **Personal Access Token** :

1. Allez sur https://github.com/settings/tokens/new
2. Cochez `repo`
3. Générez et **copiez le token**
4. Utilisez comme "mot de passe" quand Git demande

### SSH (clé privée - RECOMMANDÉ)

```powershell
# Générer une clé SSH
ssh-keygen -t ed25519 -C "votre_email@gmail.com"
# Appuyez sur Enter partout

# Copier la clé publique
cat ~/.ssh/id_ed25519.pub | clip

# Ajouter sur GitHub : https://github.com/settings/keys
# Coller la clé → "Add SSH key"

# Utiliser SSH pour pousser
git remote set-url origin git@github.com:VOTRE_PSEUDO/e-store.git
git push
```

---

## ✅ Vérifier que ça a marché

**Sur GitHub :**
1. Allez sur https://github.com/VOTRE_PSEUDO/e-store
2. Vous devriez voir votre code

**En local :**
```powershell
git log --oneline
# Devrait afficher votre commit
```

---

## 🚨 Troubleshooting

| Erreur | Cause | Solution |
|--------|-------|----------|
| `remote: Invalid username or password` | Auth échouée | Utiliser token GitHub (HTTPS) ou clé SSH |
| `fatal: remote origin already exists` | Remote déjà ajouté | `git remote remove origin` puis réessayer |
| `! [rejected] main -> main` | Branch out of sync | `git pull origin main` puis `git push` |
| `nothing to commit` | Pas de changements | Modifier des fichiers, puis `git add .` |

---

## 📚 Ressources

- **Git Docs** : https://git-scm.com/doc
- **GitHub Docs** : https://docs.github.com/en
- **Personal Access Token** : https://github.com/settings/tokens/new
- **SSH Keys** : https://github.com/settings/keys

---

**Besoin d'aide ? Consulte [GITHUB_PUSH.md](GITHUB_PUSH.md) pour plus de détails.**

Bon courage ! 🚀
