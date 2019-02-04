# Création d'un composant Vue de contenu pour le Hub Station

N'importe quel composant Vue peut être utilisé en tant que contenu du hub. 
Néanmoins, certains éléments de configuration peuvent être ajoutés pour intéragir avec le Hub Station 

## Routes du composant
Le composant peut définir ses propres routes de navigation qui seront alors utilisées au sein du Hub.
### Déclaration des routes
La déclaration des routes du composant est fournie par la méthode `getRoutesConfig`.
Cette méthode renvoie la liste des routes du composant sous forme d'un tableau.
Le format utilisé pour chaque route est celui utilisé par la librairie `vue-router`.
#### Exemple
```js
export default {
  name: "UserTabsComponent",
  methods: {
    getRoutesConfig() {
      return [
        {
          path: "infos",
          name: "UserInfos",
          component: UserInfosComponent,
          children: [
            {
              path: ":userId",
              component: UserDetailsComponent
            }
          ]
        },
        {
          path: "contact",
          component: UserContactComponent
        }
      ]
      
    }
  }
}
```

### Utilisation du routeur
#### `hub-router-link` et `hub-router-view`
Pour utiliser des liens de routage ainsi que la vue associée, il convient d'utiliser des slots nommés respectivement `hubRouterLink` et `hubRouterView`

##### Exemple


### Évènements 
`storeChanged` => hook vue

### Traduction