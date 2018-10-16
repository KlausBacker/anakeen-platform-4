# development-center

Api and ui to see anakeen platform configuration.

## Dev

### Category development

Create the Vue component in the path `src/vendor/Anakeen/DevelopmentCenter/vue/sections`.
The component must expose its own routes to connect in the router.

Example : 

In `src/vendor/Anakeen/DevelopmentCenter/vue/sections` :

```text
sections
       |      
       |Security
       |    |index.js
       |    |Security.vue
       |    |Subsections
       |    |       |
       |    |       |Infos.vue
       |    |       |Accesses.vue
       |    |       |Roles.vue
       |    |       |SmartStructures.vue
       |    |       |StructureContent.vue
       |    |       |Routes.vue      
```

Where the `index.js` file exports a Vue Router valid configuration :

`Security/index.js`
```javascript
// Route definition export for Security section
import Security from "./Security.vue";
import Roles from "./Subsections/Roles.vue";
import Routes from "./Subsections/Routes.vue";
import SmartStructures from "./Subsections/SmartStructures.vue";
import SSContent from "./Subsections/StructureContent.vue";
import Infos from "./Subsections/Infos.vue";
import Infos from "./Subsections/Accesses.vue";

export default {
  name: "Security",
  path: "security",
  component: Security,
  children: [
    {
      name: "Security::Roles",
      path: "roles",
      component: Roles
    },
    {
      name: "Security::SmartStructures",
      path: "smartStructures",
      component: SmartStructures,
      children: [
        {
          name: "Security::SmartStructures::name",
          path: ":ssName", // Variable in the URL
          component: SSContent,
          children: [
            {
              name: "Security::SS::Info",
              path: "infos",
              component: Infos,
              props: true // Optional, allows to access to url params as vue component props
            },
            {
              name: "Security::SS::Accesses",
              path: "accesses",
              component: Accesses
            }
          ]
        }
      ]
    },
    {
      name: "Security::Routes",
      path: "routes",
      component: Routes
    }
  ]
};
```

If sub routes are defined and route to another Vue component, the tag `<router-view>` (or `<router-multi-view>` to keep the DOM of the deactivated route alive) will be replaced by this sub route component.
`<router-link>` allows to route to the sub route : 

`Security/Security.vue`
```vue
<template>
    <div class="security-plugin">
        <nav class="security-nav">
            <!--Route to named route -->
            <router-link :to="{name: 'Security::Roles'}">Roles</router-link>
            <router-link :to="{name: 'Security::SmartStructures'}">Smart Structures</router-link>
            <router-link :to="{name: 'Security::Routes'}">Routes</router-link>
        </nav>
        <router-multi-view class="security-content"></router-multi-view>
    </div>
</template>

<script>
  export default {
    name: "security"
  }
</script>
```

Use the Smart Structure List component :

`Security/Subsections/SmartStructures.vue`
```vue
<template>
    <div>
        <h3>Smart Structure Security Configuration</h3>
        <ss-list routeName="Security::SmartStructures::Info"
                 routeParamField="ssName"
                 smartStructureCategory="vendor"
                 position="left"
        >
        </ss-list>
    </div>
</template>

<script>
    import SSList from "../../components/SSList/SSList.vue";
  export default {
    name: "SmartStructures",
    components: {
      "ss-list": SSList
    }
  }
</script>
```
Get url params in the component as props :

`Security/Subsections/Infos.vue`
```vue
<template>
    <h1>Infos for {{ssName}}</h1>
</template>

<script>
    import { mapGetters } from "vuex";
  export default {
    name: "Infos",
    props: ["ssName"], // if props sets to "true" in the route definition
    computed: {
      ...mapGetters([
        "currentStoredRoute"
      ]),
      structure() {
        // Store getter can also be used to access url infos
        return this.currentStoredRoute.params.ssName; // or this.$store.state.route.currentRoute.params.ssName
      }
    }
  }
</script>
```

### Errors management

#### From a vue component

```javascript
export default {
  data() {
    return {
      error: "An error has occured"
    };
  },
  mounted() {
    if (this.error) {
      this.$store.dispatch("displayError", {
        title: "Error",
        textContent: this.error,
        type: "success" // Optional, "error" by default
      })
    }
  }
}
```