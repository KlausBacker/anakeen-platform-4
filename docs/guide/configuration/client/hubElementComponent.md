# Le composant Hub Element

## Description
Le composant Hub Element correspond à un élément du Hub. Il définit la vue de l'entrée du hub dans sa totalité.
Ce composant est abstrait et ne possède pas de template par défaut. Il est destiné à être hérité. 
Le composant fournit plusieur propriétés Vue permettant de gérer l'affichage d'une entrée du hub.

::: tip ASTUCE
Le composant Hub Element utilise le mixin HubElementMixin. Ce mixin peut être utilisé à la place d'un héritage (extends) du composant HubElement.
:::

## Propriétés
### `displayType: string`
Type d'affichage du hub element. Cette propriété peut contenir 3 valeurs : 
* "COLLAPSED": Le composant affiche son template dans le dock lorsqu'il est replié
* "EXPANDED": Le composant affiche son template dans le dock lorsqu'il est déplié
* "CONTENT": Le composant affiche son template comme contenu du hub
### `parentPath: string`
Route d'accès à l'élément parent. Cette propriété doit être utilisé pour résoudre les sous routes de navigation éventuellement utilisées dans le composant.

 
## Méthodes
### `resolveHubSubPath(path)`
Retourne le chemin résolu à partir de la propriété `parentPath`.

## Computed
### `isDockCollapsed: boolean`
Détermine si le composant est affiché dans le dock replié

### `isDockExpanded: boolean`
Détermine si le composant est affiché dans le dock déplié

### `isHubContent: boolean`
Détermine si le composant est affiché en tant que contenu

::: warning NOTE
Les trois propriétés `computed` sont en exclusions mutuelles, ainsi une seule d'entre elles peut être égale à `true`
:::

## Déclaration de sous routes 
### hubRoutes
Lorsqu'un composant hérite de HubElement ou utilise HubElementMixin, il peut déclarer des sous routes sous la forme d'un tableau dans l'attribut `hubRoutes`.
Le format de chaque route est celui utilisé par la librairie `vue-router`.
#### Exemple
```js
import SubSection from "./SubSection.vue";
import ElementView from "./ElementView.vue";

export default {
    name: "my-hub-entry",
    hubRoutes: [
      {
          name: "SubSection",
          path: "mysubsection",
          component: SubSection,
          children: [
            {
                name: "ElementView",
                path: ":elementId",
                component: ElementView
            }
          ]
      }
    ]
}
```
