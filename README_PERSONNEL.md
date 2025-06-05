# Documentation Interface Personnel

## Vue d'ensemble

L'interface Personnel est une application web permettant aux utilisateurs de type Personnel de visualiser et mettre à jour l'état des bunkers de leur site assigné. Cette interface est réservée aux utilisateurs ayant le rôle `ROLE_PERSONNEL`.

## Contrôleurs

### DashboardController

`src/Controller/Personnel/DashboardController.php` - Gère l'affichage du tableau de bord

```php
// Fonctionnalités principales :
// - Affichage du tableau de bord pour le personnel
// - Récupération automatique du site associé à l'utilisateur connecté
// - Passage des bunkers à la vue
```

### SimpleAjaxController

`src/Controller/Personnel/SimpleAjaxController.php` - Gère les appels AJAX pour les bunkers

```php
// Fonctionnalités principales :
// - Méthode listBunkers() : Renvoie la liste des bunkers en JSON
// - Méthode updateBunker() : Met à jour l'état d'un bunker
// - Validation des droits d'accès au site
// - Journalisation des modifications
```

## Templates

### dashboard.html.twig

`templates/personnel/dashboard.html.twig` - Interface principale du personnel

Ce template contient :
- Une section d'en-tête avec informations utilisateur
- Un affichage des bunkers sous forme de cartes et tableau
- Un système de gestion d'état via sélecteurs
- Des scripts JavaScript pour les appels AJAX

## JavaScript

Le dashboard contient du JavaScript qui :

1. **Initialise l'interface**
   ```javascript
   document.addEventListener('DOMContentLoaded', () => {
       // Initialisation et chargement des bunkers
       loadBunkers();
   });
   ```

2. **Charge les bunkers via AJAX**
   ```javascript
   async function loadBunkers() {
       try {
           const response = await fetch('/personnel/api/bunkers');
           const data = await response.json();
           updateBunkersDisplay(data);
       } catch (error) {
           console.error('Erreur lors du chargement des bunkers:', error);
       }
   }
   ```

3. **Gère la mise à jour des états**
   ```javascript
   async function updateBunkerState(bunkerId, newState) {
       const data = {
           id: bunkerId,
           etat: newState
       };
       
       try {
           const response = await fetch('/personnel/api/update-bunker', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/x-www-form-urlencoded',
               },
               body: new URLSearchParams(data)
           });
           
           const result = await response.json();
           
           if (result.success) {
               // Mise à jour réussie
               loadBunkers(); // Recharger les données
           } else {
               console.error('Erreur:', result.message);
           }
       } catch (error) {
           console.error('Erreur réseau:', error);
       }
   }
   ```

## Guide d'utilisation

### Connexion

1. Connectez-vous avec vos identifiants de type Personnel
2. Vous serez automatiquement redirigé vers le tableau de bord personnel

### Visualisation des bunkers

- Les bunkers de votre site sont affichés en haut dans des cartes
- Un tableau récapitulatif est affiché en dessous

### Modification des états

1. Pour changer l'état d'un bunker :
   - Utilisez les sélecteurs d'état dans les cartes ou le tableau
   - Sélectionnez le nouvel état ("Pas de retard", "Retard", "Hors service", etc.)
2. La modification est immédiatement envoyée au serveur
3. L'interface se met à jour automatiquement

## Sécurité

- Chaque utilisateur Personnel ne peut voir et modifier que les bunkers de son site assigné
- L'API de mise à jour vérifie que le bunker appartient bien au site de l'utilisateur
- Les appels AJAX utilisent des requêtes POST simples sans CSRF token (simplifié)
- L'authentification est gérée par le système de sécurité Symfony

## Journalisation

Toutes les modifications sont enregistrées dans la table `logs` avec :
- L'identifiant du personnel ayant effectué la modification
- La date et l'heure
- L'action effectuée
- Le bunker concerné
