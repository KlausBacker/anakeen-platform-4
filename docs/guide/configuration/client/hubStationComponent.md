# Le composant Hub Station

## Description
Le composant Hub Station est le composant de plus haut niveau qui instancie l'interface du hub 
à partir de la configuration qui lui est passée par propriété.

## Propriétés
### `baseUrl: string`
Le chemin permettant d'accéder au hub station. La valeur permet de définir les routes d'accès aux entrées du hub.

### `config: object`
Objet représentant la configuration du hub.

### `config.instanceName: string`
Nom de l'instance du hub station

### `config.routerEntry: string`
Route d'accès au hub station

### `config.hubElements: object[]`
Chaque élément de ce tableau correspond à une entrée du hub et prend la forme d'un objet aux caractéristiques suivantes :

#### `hubElements.position: object`
Définie la position de l'élément dans le Hub Station. Elle désigne le dock utilisé pour pour afficher l'élément et l'emplacement de l'élément dans ce même dock.
Le format de la propriété est le suivant : 
```json5
{
  "dock": "LEFT", // "RIGHT", "TOP" ou "BOTTOM"
  "innerPosition": "CENTER", // "HEADER" ou "FOOTER",
  "order": null // un nombre qui précise la priorité d'ordonnancement de l'entrée
}
```
  
#### `hubElements.component: object`
Spécifie le composant Vue à utiliser.
L'objet doit respecter le format suivant :
```json5
{
  "name": "MyComponent", // Nom du composant tel qu'il a été défini lors de son enregistrement
  "props": {
    "myComponentProp": "aPropValue",
    "otherProp": 23
  }
}
```
::: warning NOTE
Pour fonctionner, le composant Vue référencé par la propriété `config.component` devra être enregistré globalement à l'aide de la fonction `Vue.component`
:::

#### `hubElements.entryOptions: object`
Cette propriété regroupe les options de paramétrage de l'entrée du hub. Elle suit le format :
```json5
{
  route: "myEntryRoute", // La route permettant d'accéder à l'entrée, relative à la propriété `baseUrl`
  selectable: true, // l'entrée est sélectionnable
  activated: false, // l'entrée n'est pas sélectionnée par défaut
  activatedOrder: false // ordre de priorité pour la sélection par défaut (si plusieurs entrèes sont marquées comme activated)
}
```

## Méthodes
### `addHubElement(config)`
Ajoute un élément dans le hub.

### `expandDock(dockPosition)`
Déplie le dock du hub spécifié en paramètre

### `collapseDock(dockPosition)`
Replie le dock spécifié en paramètre

## Évènements
### `beforeRouteChange`
Déclenché avant un changement de route au sein du Hub Station

### `afterRouteChange`
Déclenché après un changement de route au sein du Hub Station

### `hubElementReady`
Déclenché lorsqu'un élément du hub est inséré dans le hub

### `hubElementSelected`
Déclenché lorsqu'un élément du hub est sélectionné

### `hubNotify`
Déclenché lorsqu'un le Hub Station notifie un message. L'objet récupéré lors de l'emission de cet évènement est de la forme suivante :
```json5
{
  type: "info", // Type de notification parmi: "info", "notice", "success", "warning", "error" 
  content: {
    textContent: "Un message d'information", // ou htmlContent: "<em>Un message d'information important</em>"
    title: "Titre du message",
  },
  options: {
    displayTime: 1000, // temps d'affichage en ms de la notification (5000ms par défaut)
    closable: false, // La notification peut être fermée via l'ui ou non (true par défaut)
  }
}
```