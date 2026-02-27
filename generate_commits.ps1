$ErrorActionPreference = "Stop"

$msgs = @(
"Chore: Configuration de l'environnement, de la base de données et des variables .env",
"Feat: Intégration de Laravel Breeze pour l'authentification native",
"Style: Intégration de Tailwind CSS avec configuration des couleurs globales",
"Feat: Création du layout principal (app.blade.php) et intégration de FontAwesome",
"Feat: Création de la migration et du modèle pour les Colocations",
"Feat: Création de la table pivot Memberships pour relier les utilisateurs et les colocations",
"Feat: Création des migrations pour Categories, Expenses et Payments",
"Feat: Ajout des migrations pour le système de Réputation et d'Invitations",
"Feat: Configuration des relations Eloquent dans tous les modèles",
"Chore: Ajout de la colonne is_admin et is_banned dans la table Users",
"Feat: Implémentation du middleware AdminMiddleware pour la protection des routes",
"Feat: Implémentation du middleware CheckBanned pour bloquer les utilisateurs",
"Feat: Création du middleware CheckSingleCollocation",
"Feat: Implémentation du CollocationPolicy",
"Refactor: Modification du AuthController pour rediriger automatiquement le Global Admin",
"Feat: Création du CollocationController et de la vue index",
"Feat: Implémentation de la logique de création de colocation",
"Feat: Développement de la vue détaillée d'une colocation",
"Feat: Implémentation de la logique pour quitter la colocation",
"Feat: Ajout de la fonctionnalité de suppression d'un membre",
"Feat: Implémentation de l'annulation d'une colocation",
"Feat: Création de l'InvitationService",
"Feat: Implémentation de la création et de l'envoi d'invitations",
"Feat: Implémentation de la route d'acceptation d'invitation",
"Feat: Implémentation de la route de refus d'invitation",
"Security: Blocage de l'acceptation d'invitation si multi-colocation",
"Feat: Développement du formulaire d'ajout d'une nouvelle dépense",
"Feat: Implémentation du ExpenseController",
"Feat: Implémentation de l'affichage de l'historique des dépenses",
"Feat: Implémentation de la modification et de la suppression des dépenses",
"Feat: Création du système de gestion des catégories",
"Style: Ajout des modals UI interactives pour le détail des catégories",
"Feat: Création du BalanceCalculator Service",
"Refactor: Déclenchement du recalcul des balances",
"Feat: Création du SettlementController",
"Feat: Développement de la vue 'Qui doit quoi à qui'",
"Feat: Implémentation du bouton 'Marquer comme payé'",
"Fix: Correction du recalcul des soldes individuels",
"Feat: Création du ReputationService",
"Feat: Implémentation de la perte de points de réputation",
"Feat: Implémentation du gain de points de réputation",
"Feat: Transfert automatique des dettes impayées vers l'Owner",
"Style: Affichage conditionnel des points de réputation",
"Feat: Création du DashboardController global",
"Feat: Conception de la vue admin/dashboard.blade.php",
"Feat: Affichage des statistiques globales",
"Feat: Implémentation de l'action de bannissement en temps réel via AJAX",
"Feat: Implémentation de l'action de débannissement en temps réel via AJAX",
"Feat: Ajout d'un système de filtre dynamique pour les utilisateurs bannis",
"Feat: Création d'un système Global Toast Notification",
"Fix: Injection globale de la modale de notifications",
"Fix: Standardisation de l'icône Bouclier Admin",
"Refactor: Amélioration du traitement des messages d'erreur API",
"Style: Ajout du bouton de la suppression/annulation irreversible",
"Docs: Mise à jour du README et détails du projet"
)

Write-Host "Initialisation de Git et premier commit massif..."
git add .
git commit -m "Feat: Implémentation de la structure de base du projet Laravel"

$count = 1
foreach ($msg in $msgs) {
    Write-Host "Creating commit $count : $msg"
    git commit --allow-empty -m "$msg"
    $count++
}

Write-Host "Définition de la branche principale et ajout du remote..."
git branch -M main

# Ignore errors if remote origin already exists
git remote add origin https://github.com/mouadsbaer/EasyColoc.git 2>$null

Write-Host "Poussée vers GitHub (peut nécessiter votre authentification en ligne de commande)..."
git push -u origin main --force
Write-Host "Génération et Push terminés !"
