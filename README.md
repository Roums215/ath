# Application de Gestion des Bunkers de Radiothérapie

## Vue d'ensemble du système

Cette application Symfony 6.4 gère l'état des bunkers de radiothérapie et permet une visualisation en temps réel de leur statut. Elle est organisée autour de trois interfaces principales :

1. **Interface Administrateur** - Gestion complète des entités via EasyAdmin
2. **Interface Personnel** - Tableau de bord pour mise à jour des états des bunkers
3. **Interface ATH** - Affichage public des états des bunkers (pour écrans muraux)

## Structure de l'application

```
radiotherapie2025/
├── src/
│   ├── Controller/
│   │   ├── Admin/             # Contrôleurs EasyAdmin pour administration
│   │   ├── Personnel/         # Contrôleurs pour interface personnel
│   │   └── AthController.php  # Contrôleur d'affichage public
│   ├── Entity/                # Entités Doctrine (Bunker, Site, Personnel, etc.)
│   └── Repository/            # Repositories Doctrine
├── templates/
│   ├── admin/                 # Templates EasyAdmin (surcharges)
│   ├── personnel/             # Templates pour l'interface personnel
│   └── ath/                   # Templates pour l'affichage public
└── assets/
    └── styles/                # Feuilles de style CSS
```

## Flux d'interactions

1. **Authentification**
   - Les utilisateurs Admin et Personnel se connectent via le formulaire de login
   - L'authenticator dirige vers leurs interfaces respectives selon le rôle

2. **Mise à jour des bunkers**
   - L'Admin peut gérer tous les bunkers de tous les sites via EasyAdmin
   - Le Personnel ne peut modifier que les bunkers de son site assigné
   - Les mises à jour sont effectuées par AJAX sans protection CSRF

3. **Affichage public**
   - L'ATH (Affichage Tête Haute) est accessible sans authentification
   - Il se rafraîchit automatiquement toutes les 60 secondes
   - Les bunkers sont présentés en rotation avec une distribution intelligente

## Sécurité

- Routes `/admin` protégées par le rôle ROLE_ADMIN
- Routes `/personnel` protégées par le rôle ROLE_PERSONNEL
- Routes `/ath` accessibles publiquement (PUBLIC_ACCESS)
- Protection CSRF active sur les formulaires d'authentification
- Protection CSRF désactivée pour les appels AJAX de mise à jour des bunkers

## Bibliothèques principales

- Symfony 6.4 - Framework PHP
- EasyAdmin - Interface d'administration
- Doctrine - ORM pour la persistence des données

Pour plus de détails sur chaque composant, consultez les README spécifiques :
- [README Admin](README_ADMIN.md)
- [README Personnel](README_PERSONNEL.md)
- [README ATH](README_ATH.md)
