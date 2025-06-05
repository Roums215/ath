# Documentation Interface Administrateur

## Vue d'ensemble

L'interface administrateur est basée sur EasyAdmin et permet la gestion complète de toutes les entités du système. Cette interface est réservée aux utilisateurs ayant le rôle `ROLE_ADMIN`.

## Contrôleurs

### AdminCrudController

`src/Controller/Admin/AdminCrudController.php` - Gère les administrateurs

```php
// Fonctionnalités principales :
// - CRUD sur les entités Admin
// - Validation des données
// - Hashage des mots de passe
```

### SiteCrudController

`src/Controller/Admin/SiteCrudController.php` - Gère les sites de radiothérapie

```php
// Fonctionnalités principales :
// - CRUD sur les entités Site
// - Upload et gestion des logos
// - Association avec les bunkers et personnel
```

### BunkerCrudController

`src/Controller/Admin/BunkerCrudController.php` - Gère les bunkers

```php
// Fonctionnalités principales :
// - CRUD sur les entités Bunker
// - Gestion des états (Pas de retard, Retard, Hors service, etc.)
// - Association avec les sites
```

### PersonnelCrudController

`src/Controller/Admin/PersonnelCrudController.php` - Gère les utilisateurs personnel

```php
// Fonctionnalités principales :
// - CRUD sur les entités Personnel
// - Hashage des mots de passe
// - Association avec les sites
```

### DashboardController

`src/Controller/Admin/DashboardController.php` - Configuration du tableau de bord admin

```php
// Fonctionnalités principales :
// - Configuration des menus
// - Définition des entités gérées
// - Personnalisation de l'interface
```

## Guide d'utilisation

### Gestion des sites

1. Accédez à "Sites" dans le menu
2. Créez un nouveau site avec son nom et logo
3. Les bunkers associés apparaîtront dans la liste de détails

### Gestion des bunkers

1. Accédez à "Bunkers" dans le menu
2. Créez ou modifiez un bunker en définissant :
   - Nom du bunker
   - Site associé
   - État actuel (par défaut "Pas de retard")

### Gestion des utilisateurs

1. Pour créer un administrateur :
   - Accédez à "Admins" dans le menu
   - Remplissez les informations (nom, mot de passe)

2. Pour créer un utilisateur personnel :
   - Accédez à "Personnel" dans le menu
   - Remplissez les informations (nom, mot de passe)
   - Associez au site correspondant

## Journalisation

Toutes les modifications sur les bunkers sont tracées dans la table `logs` avec :
- Utilisateur ayant effectué l'action
- Date/heure
- Action réalisée
- Entité concernée

## Sécurité

L'interface administrateur est protégée par :
- Un contrôle d'accès sur le préfixe `/admin`
- Une authentification via `LoginFormAuthenticator`
- Une vérification du rôle `ROLE_ADMIN`
