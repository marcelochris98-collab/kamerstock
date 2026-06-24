# Guide de Vérification Manuelle Multi-Tenant (KamerStock)

Ce document fournit la procédure détaillée étape par étape pour valider manuellement le comportement de la plateforme multi-tenant dans un environnement de staging ou de production.

---

## 1. Accès Centralisé Landlord (Console d'Administration)

### Objectif
Vérifier la sécurité et l'accès à la console super admin.

### Étapes à suivre
1. Naviguer vers l'adresse `/landlord/login`.
2. Tenter une connexion avec des identifiants invalides :
   * **Attendu** : Erreur de validation, rejet de la connexion.
3. Se connecter avec l'adresse email et le mot de passe du super administrateur.
   * **Attendu** : Connexion réussie, redirection vers `/landlord/dashboard`.
4. Naviguer dans les menus de la barre latérale (Boutiques, Plans, Abonnements, Paiements, Statistiques, Sauvegardes, Journal d'audit).
   * **Attendu** : Aucun plantage (erreur 500) sur aucune de ces pages.

---

## 2. Création et Provisionnement d'une Boutique

### Objectif
S'assurer qu'une nouvelle boutique est correctement initialisée (base de données, tables et utilisateur par défaut).

### Étapes à suivre
1. Dans la console Landlord, aller sur **Boutiques (Tenants)** > **Créer une boutique**.
2. Remplir le formulaire avec des données valides (nom, slug unique, email propriétaire, type de commerce).
3. Soumettre le formulaire.
4. Dans la liste des boutiques, vérifier l'état du provisionnement :
   * **Attendu** : Le statut passe à `database_created` puis `migrated` (ou `prepared` si la création automatique est différée).
5. Se connecter à la base de données serveur et lister les bases de données :
   * **Attendu** : Une nouvelle base de données préfixée par la configuration (ex: `kamerstock_tenant_[slug]`) existe.
   * **Attendu** : Toutes les tables de l'application KamerStock y sont migrées.
   * **Attendu** : La table `users` de cette base contient l'utilisateur administrateur de la boutique configuré dans le formulaire.

---

## 3. Isolation Complète des Données (Multi-Tenant)

### Objectif
Garantir qu'une boutique ne peut absolument pas voir ou modifier les données d'une autre boutique.

### Étapes à suivre
1. Ouvrir deux fenêtres de navigateur distinctes (ou une fenêtre normale et une privée).
2. Se connecter sur la **Boutique A** (ex: `boutique-a.kamerstock.local` ou via paramètre d'URL `?tenant=boutique-a`).
3. Créer un produit "Produit Boutique A" et une catégorie "Catégorie Boutique A".
4. Se connecter sur la **Boutique B** (`boutique-b.kamerstock.local` ou `?tenant=boutique-b`).
5. Consulter la liste des produits et des catégories.
   * **Attendu** : "Produit Boutique A" et "Catégorie Boutique A" **n'apparaissent pas** sur la Boutique B.
6. Essayer d'accéder directement via URL à une fiche produit ou vente appartenant à la Boutique A en étant connecté à la Boutique B :
   * **Attendu** : Retourne une erreur `404 Not Found`.

---

## 4. Accès Support Sécurisé et Journalisation

### Objectif
Vérifier qu'un administrateur Landlord peut se connecter de manière temporaire à une boutique en mode support et que tout est tracé.

### Étapes à suivre
1. Dans la console Landlord, aller sur **Maintenance & Audit** > **Accès Support** > **Créer un accès**.
2. Sélectionner une boutique, indiquer le motif et la durée (ex: 30 minutes).
3. Activer l'accès.
4. Cliquer sur le bouton **Entrer** pour cette session de support.
   * **Attendu** : Redirection vers le dashboard de la boutique choisie.
   * **Attendu** : Une bannière persistante en haut de la boutique indique que vous êtes connecté en mode Support.
   * **Attendu** : Vous pouvez naviguer sur les pages de la boutique (Produits, Ventes) avec les privilèges d'administrateur.
5. Cliquer sur le bouton **Quitter la session de support** de la bannière.
   * **Attendu** : Redirection immédiate vers la console Landlord (`/landlord/support-accesses`).
   * **Attendu** : Accès révoqué ou inactif.
6. Consulter le **Journal d'audit** (`/landlord/audit-logs`).
   * **Attendu** : Deux entrées sont présentes avec l'horodatage exact : `support_access_entered` et `support_access_exited`.

---

## 5. Enforcement des Restrictions d'Abonnement (Lecture Seule et Suspension)

### Objectif
Valider que les boutiques restreintes ou suspendues voient leurs droits limités.

### Étapes à suivre
1. Dans la console Landlord, modifier le statut d'une boutique test en **Lecture Seule** (`read_only`).
2. Se connecter sur cette boutique.
   * **Attendu** : La connexion est autorisée et la consultation des produits et rapports fonctionne.
3. Tenter de créer un produit ou d'enregistrer une vente.
   * **Attendu** : Blocage immédiat avec affichage d'un message en français indiquant que la boutique est en lecture seule suite à un abonnement expiré.
4. Dans la console Landlord, passer le statut de la boutique en **Suspendu** (`suspended`).
5. Tenter d'accéder à la boutique.
   * **Attendu** : Blocage total. Redirection vers une page d'attente `/tenant/pending` (ou message d'erreur d'accès).

---

## 6. Sauvegardes et Restaurations par Boutique

### Objectif
Valider la sauvegarde hermétique de la base de données d'une boutique.

### Étapes à suivre
1. Dans la console Landlord, aller sur **Maintenance & Audit** > **Sauvegardes DB**.
2. Cliquer sur **Lancer une sauvegarde** pour la boutique test.
   * **Attendu** : La sauvegarde s'effectue et se termine avec le statut `completed`.
3. Cliquer sur le bouton **Télécharger**.
   * **Attendu** : Téléchargement d'un fichier `.sql` ou `.zip`.
4. Inspecter le fichier de sauvegarde téléchargé :
   * **Attendu** : Le fichier ne contient que les données et schémas de la base de données de cette boutique spécifique (aucune donnée landlord ni autre boutique).

---

## 7. Commandes Diagnostics via Console (Artisan)

### Objectif
Vérifier l'intégrité de l'infrastructure via les commandes de diagnostic.

### Étapes à suivre
1. Ouvrir le terminal sur le serveur.
2. Lancer la commande : `php artisan platform:full-check`.
   * **Attendu** : Tous les tests passent au vert (`[OK]`).
   * **Attendu** : L'état général est valide.
