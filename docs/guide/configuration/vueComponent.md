# Utilisation d'un composant Vue comme entrée du hub

N'importe quel composant Vue peut être utilisé pour définir une entrée du Hub Station. 
Néanmoins, pour fonctionner correctement il est nécessaire que le composant hérite du composant `HubElement` (ou utilise le mixin `HubElementMixin` correspondant).

## Exemple

Soit le composant Vue existant :

ListView.vue
```vue
<template>
<div>
    <ul>
        <li v-for="item in listItems">{{item.label}}</li>
    </ul>
</div>
</template>
<script>
export default {
  props: {
    listItems: {
      type: Array,
      default: () => []
    }
  }
}
</script>
```

### Création du Hub Element
ListViewEntry.vue
```vue
<template>
<div>
    <div v-if="isDockCollapsed">
        <i class="fa fa-ul"></i>
    </div>
    <div v-else-if="isDockExpanded">
        <span>Liste des utilisateurs</span>
    </div>
    <div v-else-if="isHubContent">
        <list-view :listItems="items"></list-view>
    </div>
</div>
</template>
<script>
import ListView from "./ListView.vue"; // Le composant existant
import { HubElement } from "@anakeen/hub-components";
// ou
// import { HubElementMixin } from "@anakeen/hub-components";
export default {
  name: "list-view-entry",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    ListView
  },
  props: {
    items: { // Le composant déclare des propriétés de même type que le composant ListView
      type: Array,
      default: () => []
    }
  }
}
</script>
```

### Enregistrement du Hub Element
index.js
```js
import Vue from vue;
import ListViewEntry from "./ListViewEntry.vue";

Vue.component(ListViewEntry.name, ListViewEntry);

```

### Configuration du Hub Station
Main.vue
```vue
<template>
    <hub-station :config="config"></hub-station>
</template>
<script>
import { HubStation } from "@anakeen/hub-components";

export default {
  components: {
    HubStation
  },
  data() {
    return {
      config: [
        {
          position: {
            dock: "LEFT",
            innerPosition: "CENTER",
            order: null
          },
          component: {
            name: "list-view-entry",
            props: {
              items: [{ label: "Foo" }, { label: "Bar"} ]
            }
          },
          entryOptions: {
            selectable: true,
            selected: false
          }
        }
      ]
    }
  }
}
</script>

```