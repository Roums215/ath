# Documentation Interface ATH (Affichage Tête Haute)

## Vue d'ensemble

L'interface ATH (Affichage Tête Haute) est une interface publique destinée à l'affichage des états des bunkers sur des écrans dans les salles d'attente ou couloirs. Cette interface est accessible publiquement sans authentification.

## Contrôleur

### AthController

`src/Controller/AthController.php` - Gère l'affichage public des bunkers

```php
/**
 * Contrôleur pour l'affichage tête haute (ATH) des bunkers
 */
class AthController extends AbstractController
{
    /**
     * Affiche l'ATH pour un site donné
     */
    #[Route('/ath/{id}', name: 'ath_show')]
    public function show(Site $site): Response
    {
        return $this->render('ath/show.html.twig', [
            'site' => $site,
        ]);
    }

    /**
     * Fournit les données des bunkers en JSON
     */
    #[Route('/ath/{id}/data', name: 'ath_data')]
    public function data(Site $site): JsonResponse
    {
        $bunkers = array_map(
            static fn (Bunker $b) => [
                'nom'  => $b->getBNom(),
                'etat' => $b->getBEtat() ?: 'Pas de retard',
            ],
            $site->getBunkers()->toArray()
        );

        return $this->json(['bunkers' => $bunkers]);
    }
}
```

Le contrôleur comporte deux méthodes principales :
- `show()` : Affiche le template de l'ATH pour un site donné
- `data()` : Fournit les données des bunkers au format JSON pour le rafraîchissement AJAX

## Template

### show.html.twig

`templates/ath/show.html.twig` - Interface d'affichage des bunkers

Ce template contient :
- Une structure HTML responsive
- Des styles CSS intégrés pour l'affichage plein écran
- Un script JavaScript pour la gestion des slides et du rafraîchissement

## JavaScript

Le template ATH contient un script JavaScript qui :

1. **Charge les données des bunkers**
   ```javascript
   async function loadData() {
       const r     = await fetch(URL_DATA);
       const json  = await r.json();
       const oldNb = groups.length;

       groups = groupBunkers(json.bunkers);
       
       // [Suite du code pour gestion des slides]
   }
   ```

2. **Regroupe intelligemment les bunkers par slides**
   ```javascript
   function groupBunkers(list) {
       const n = list.length;

       if (n <= 3) {
           // 1, 2 ou 3 → tout sur un seul slide
           return [list];
       }

       if (n === 4) {
           return [list.slice(0, 2), list.slice(2)];      // 2 + 2
       }
       
       // [Autres cas de regroupement]
   }
   ```

3. **Gère la rotation des slides**
   ```javascript
   function next() {
       current = (current + 1) % groups.length;
       show();
       timerId = setTimeout(next, DISPLAY_TIME);
   }
   
   function startCarousel() {
       show();
       clearTimeout(timerId);

       if (groups.length > 1) {
           timerId = setTimeout(next, DISPLAY_TIME);
       }
   }
   ```

4. **Affiche les bunkers avec code couleur**
   ```javascript
   function statusClass(etat) {
       const e = etat.toLowerCase();
       if (e === 'pas de retard')        return 'ok';
       if (e.startsWith('retard'))       return 'delay';
       if (e === 'hors service')         return 'hs';
       return 'maintenance';
   }
   ```

5. **Rafraîchit les données périodiquement**
   ```javascript
   document.addEventListener('DOMContentLoaded', () => {
       loadData();                 // première charge
       setInterval(loadData, REFRESH_TIME);  // refresh silencieux
   });
   ```

## CSS

Le CSS intégré comprend :
- Styles pour l'affichage plein écran (100% largeur/hauteur)
- Layout Flexbox pour une structure responsive
- Code couleur pour les différents états :
  - Vert : "Pas de retard"
  - Orange : "Retard"
  - Rouge : "Hors service"
  - Bleu : "Maintenance"
- Animations de transition entre les slides

## Guide d'utilisation

### Accès à l'ATH

Pour afficher l'ATH d'un site :
1. Accédez à l'URL `/ath/{id}` où `{id}` est l'identifiant du site
2. L'écran s'affiche automatiquement en plein écran

### Comportement

- L'écran se rafraîchit automatiquement toutes les 60 secondes
- Les bunkers sont affichés par groupes adaptés à leur nombre total
- Si plus de bunkers que ne peut contenir un écran, rotation automatique toutes les 10 secondes
- Code couleur pour identifier rapidement les états

## Configuration

Vous pouvez ajuster les paramètres dans le template `show.html.twig` :

```javascript
const DISPLAY_TIME = 10000;  // Durée d'affichage de chaque slide (10s)
const REFRESH_TIME = 60000;  // Intervalle de rafraîchissement des données (60s)
```

## Sécurité

- L'interface ATH est accessible publiquement (configuré dans `security.yaml`)
- Seules les données de lecture sont exposées (pas de risque de modification)
- L'identifiant du site est requis dans l'URL
