# 🚀 VISUAL SEARCH - Dépannage de l'erreur "Not Found"

L'erreur **"Not Found"** signifie qu'Apache ne trouve pas la page.

---

## ✅ Étapes pour corriger

### **Étape 1: Redémarrer Apache** (importante!)

XAMPP Control Panel:
1. Cliquer sur "Apache" → **Stop**
2. Attendre 2-3 secondes
3. Cliquer sur "Apache" → **Start**
4. Attendre que ça dise "Running"

Ou via PowerShell:
```powershell
# Arrêter
Get-Process apache2 | Stop-Process -Force

# Patienter
Start-Sleep -Seconds 3

# Redémarrer via XAMPP
# Ou lancer Apache manuellement via XAMPP GUI
```

### **Étape 2: Tester Apache fonctionne**

Accéder à:
```
http://localhost/
```

Vous devriez voir la page XAMPP.

### **Étape 3: Vérifier les fichiers**

Accéder à:
```
http://localhost/projet_php/e-store/public/test-visual-search.php
```

Cela affiche un rapport de diagnostic.

**Résultat attendu**:
```
✅ APACHE FONCTIONNE!

📂 Fichiers Visual Search:
  ✅ config/visual-search.php
  ✅ app/core/ImageUpload.php
  ✅ app/core/VisualSearch.php
  ✅ public/api/visual-search.php
  ...
```

### **Étape 4: Vérifier la configuration**

Éditer le fichier `.env` à la racine:

```env
# Ces deux lignes DOIVENT avoir une valeur:
CLARIFAI_API_KEY=votre_clé_api_ici
CLARIFAI_API_URL=https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs
```

⚠️ **Important**: La clé API peut être vide au départ (ça va afficher un message), mais l'URL ne doit PAS être vide.

### **Étape 5: Accéder à Visual Search**

Une fois les étapes précédentes OK:

```
http://localhost/projet_php/e-store/public/visual-search.php
```

---

## 🐛 Si ça ne marche toujours pas

### Cause 1: Apache ne démarre pas

**Signe**: "Running" n'apparaît pas dans XAMPP

**Solution**:
1. Ouvrir l'invite de commande en admin
2. Aller à: `C:\xampp\apache\bin`
3. Taper: `httpd.exe`
4. S'il y a une erreur, c'est le port 80 qui est utilisé

### Cause 2: MySQL n'est pas lancé

**Signe**: Erreur "database connection"

**Solution**:
1. XAMPP Control Panel
2. Cliquer "MySQL" → **Start**
3. Attendre "Running"

### Cause 3: Fichiers mal créés

**Solution**:
1. Vérifier que tous les fichiers existent:
   - `config/visual-search.php`
   - `app/core/ImageUpload.php`
   - `app/core/VisualSearch.php`
   - `public/visual-search.php`
   - `public/api/visual-search.php`

2. Si manquants, copier depuis la documentation

### Cause 4: Chemin incorrect

Le chemin doit être EXACT:
```
✅ CORRECT: http://localhost/projet_php/e-store/public/visual-search.php
❌ FAUX:    http://localhost/e-store/visual-search.php
❌ FAUX:    http://localhost:80/projet_php/e-store/public/visual-search.php
```

### Cause 5: Cache navigateur

**Solution**:
1. Ouvrir les développeur (F12)
2. Onglet "Application"
3. Cliquer "Clear storage"
4. Ou: Ctrl+Shift+Delete

---

## ✅ Checklist finale

- [ ] Apache redémarré et running
- [ ] MySQL running
- [ ] `.env` existe et a les bonnes variables
- [ ] Tous les fichiers PHP existent
- [ ] Tous les dossiers existent:
  - `tmp/visual-search/`
  - `public/uploads/visual-search/`
  - `public/api/`
- [ ] Page test accessible: `/public/test-visual-search.php`
- [ ] Page visual-search accessible: `/public/visual-search.php`

---

## 📞 Support

Si ça ne marche toujours pas:

1. Vérifier les logs Apache:
   ```
   C:\xampp\apache\logs\error.log
   C:\xampp\apache\logs\access.log
   ```

2. Vérifier les logs PHP:
   ```
   C:\xampp\php\error.log
   ```

3. Vérifier MySQL:
   ```
   C:\xampp\mysql\data\*.err
   ```

---

**Recommencez et ça devrait marcher! 🚀**

Si question, consultez la documentation: `docs/VISUAL_SEARCH_*.md`
