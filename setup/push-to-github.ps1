#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Script d'automatisation : Pousser le projet e-store sur GitHub en un clic
.DESCRIPTION
    Ce script initialise Git, ajoute les fichiers, crée un commit et pousse sur GitHub.
.PARAMETER GitHubUrl
    URL du repo GitHub (ex: https://github.com/VOTRE_PSEUDO/e-store.git)
.PARAMETER CommitMessage
    Message du commit (ex: "Initial commit: E-Store with AI image search")
.EXAMPLE
    .\push-to-github.ps1 -GitHubUrl "https://github.com/VOTRE_PSEUDO/e-store.git"
    .\push-to-github.ps1 -GitHubUrl "git@github.com:VOTRE_PSEUDO/e-store.git"
#>

param(
    [string]$GitHubUrl = "",
    [string]$CommitMessage = "feat: Add AI image search functionality"
)

# Couleurs pour l'output
$ColorSuccess = "Green"
$ColorError = "Red"
$ColorInfo = "Cyan"

Write-Host "`n🚀 E-Store GitHub Push Automation`n" -ForegroundColor $ColorInfo

# ====== ÉTAPE 1 : Vérifier Git ======
Write-Host "[1/6] Vérification de Git..." -ForegroundColor $ColorInfo
if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Git n'est pas installé. Installez-le depuis https://git-scm.com" -ForegroundColor $ColorError
    exit 1
}
Write-Host "✅ Git trouvé" -ForegroundColor $ColorSuccess

# ====== ÉTAPE 2 : Demander l'URL GitHub si nécessaire ======
if ([string]::IsNullOrWhiteSpace($GitHubUrl)) {
    Write-Host "`n[2/6] Saisie de l'URL GitHub" -ForegroundColor $ColorInfo
    $GitHubUrl = Read-Host "Entrez l'URL de votre repo GitHub (ex: https://github.com/VOTRE_PSEUDO/e-store.git)"
    
    if ([string]::IsNullOrWhiteSpace($GitHubUrl)) {
        Write-Host "❌ URL vide. Abandon." -ForegroundColor $ColorError
        exit 1
    }
} else {
    Write-Host "[2/6] URL GitHub fournie : $GitHubUrl" -ForegroundColor $ColorInfo
}

# ====== ÉTAPE 3 : Initialiser Git ======
Write-Host "`n[3/6] Initialisation de Git..." -ForegroundColor $ColorInfo

if (Test-Path .git) {
    Write-Host "ℹ️  Git est déjà initialisé" -ForegroundColor $ColorInfo
} else {
    git init
    Write-Host "✅ Dépôt Git initialisé" -ForegroundColor $ColorSuccess
}

# ====== ÉTAPE 4 : Ajouter fichiers ======
Write-Host "`n[4/6] Ajout des fichiers..." -ForegroundColor $ColorInfo
git add .
Write-Host "✅ Fichiers ajoutés (respectant .gitignore)" -ForegroundColor $ColorSuccess

# ====== ÉTAPE 5 : Commit ======
Write-Host "`n[5/6] Création du commit..." -ForegroundColor $ColorInfo
git commit -m $CommitMessage
Write-Host "✅ Commit créé : $CommitMessage" -ForegroundColor $ColorSuccess

# ====== ÉTAPE 6 : Ajouter remote et pousser ======
Write-Host "`n[6/6] Ajout du remote et push..." -ForegroundColor $ColorInfo

# Vérifier si le remote existe
$remoteExists = git remote | Select-String "origin"
if ($remoteExists) {
    Write-Host "ℹ️  Remote 'origin' existe déjà" -ForegroundColor $ColorInfo
} else {
    git remote add origin $GitHubUrl
    Write-Host "✅ Remote 'origin' ajouté" -ForegroundColor $ColorSuccess
}

# Branche main
git branch -M main

# Push
Write-Host "ℹ️  Connexion à GitHub et push en cours..." -ForegroundColor $ColorInfo
git push -u origin main

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n✅ SUCCÈS ! Votre projet est sur GitHub !" -ForegroundColor $ColorSuccess
    Write-Host "📍 Accédez-le ici : $($GitHubUrl -replace '.git$','')" -ForegroundColor $ColorInfo
} else {
    Write-Host "`n❌ Erreur lors du push. Vérifiez :" -ForegroundColor $ColorError
    Write-Host "  • Votre connexion Internet" -ForegroundColor $ColorError
    Write-Host "  • Votre authentification GitHub (token ou SSH)" -ForegroundColor $ColorError
    Write-Host "  • L'URL du repo" -ForegroundColor $ColorError
    exit 1
}

Write-Host "`n💡 Prochaines étapes :" -ForegroundColor $ColorInfo
Write-Host "  1. Partagez le lien de votre repo avec vos collaborateurs"
Write-Host "  2. Lisez le README.md pour la configuration"
Write-Host "  3. Configurez votre .env avec les variables nécessaires"
Write-Host "`n"
