# Le composant Hub Station

## Description
Le composant Hub Station est le composant de plus haut niveau qui instancie l'interface du hub 
à partir de la configuration qui lui est passée par propriété.

## Propriétés
### `config`
Tableau représentant la configuration du hub. Chaque élément de ce tableau est un objet qui dispose des clés suivantes :

#### `config.position`
Définie la position de l'élément dans le Hub Station. Elle désigne le dock utilisé pour pour afficher l'élément et l'emplacement de l'élément dans ce même dock.
  
#### `config.XXX.template`
Objet représentant les différents template du hub element.
Chaque template peut être un template HTML sous la forme d'une chaîne de caractère ou un objet représentant un composant Vue.
Dans ce cas, l'objet doit respecter le format suivant :
```json
{
  "componentName": "MyComponent",
  "props": {
    "myComponentProp": "aPropValue"
  }
}
```
##### `config.compact.template`
Définit l'affichage de l'élément dans sa forme compact
##### `config.expanded.template`
Définit l'affichage de l'élément dans sa forme étendue
##### `config.content.template`
Définit l'affichage du contenu de l'élément
##### `config.content.el`
DOMElement du hub accueillant le contenu de l'élément.

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