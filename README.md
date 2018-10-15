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
       |Parameters
       |    |index.js
       |    |Parameters.vue
       |    |General.vue
       |    |SSList.vue
       |        
       |Security
       |    |index.js
       |    |Security.vue      
```

Where the `index.js` file export a Vue Router valid configuration :

`Parameters/index.js`
```javascript
import Parameters from "./Parameters.vue";
import SSList from "./SSList.vue";
import General from "./General.vue";

export default {
  label: "Parameters", // Only available for the top level route
  name: "Parameters", // name of the route (recommended for nested routing)
  path: "parameters", // path of the route (required) (relative to parent),
  component: Parameters, // The Vue component to route to (optional, if not present the component display is the parent route component)
  children: [ // Eventually, some sub routes of the component
    {
      name: "Parameters::general",
      path: "general",
      component: General
    },
    {
      name: "Parameters::smartStructure",
      path: "smartstructure",
      component: SSList,
      children: {
        name: "Parameters::smartStructureName"
        path: ":ssname" // Url params are defined with ":paramname"
        // Use this.$router.push({name: "Parameters::smartStructureName", params: { ssname: "DEVSTRUCTURE" }}) to route the app programmatically 
      }
    }
  ]
}
```

If sub routes are defined and route to another Vue component, the tag `<router-view>` will be replaced by this sub route component.
`<router-link>` allows to route to the sub route  

`Parameters/Parameters.vue`
```vue
<template>
    <div>
        <h1>Parameters Section</h1>
        <ul class="menu">
            <router-link :to="{name: 'General'}">Général</router-link>
            <router-link :to="{name: 'SmartStructure'}">Smart Structure</router-link>
        </ul>    
        <router-view></router-view>
    </div>
</template>
<style></style>
<script>
    export default {
      mounted() {
        // Access to the route params
        const ssname = this.$router.currentRoute.params.ssname;
      }
    }
</script>
```

#### Full complex example

##### File hierarchy
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

##### Security routes declaration
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
          path: ":ssName",
          component: SSContent,
          children: [
            {
              name: "Security::SS::Info",
              path: "infos",
              component: Infos
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

##### Entry point of security section
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
        <!-- Use <router-multi-view> to route and manage multiple content view silmutaneously-->
        <router-multi-view class="security-content"></router-multi-view>
    </div>
</template>

<script>
  export default {
    name: "security"
  }
</script>
```
##### Smart Structure List in subsections
`Security/Subsections/SmartStructures.vue`
```vue
<template>
    <div>
        <h3>Smart Structure Security Configuration</h3>
        <ss-list routeName="Security::SmartStructures::name"
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
##### Nested sections management (Security -> Smart Structures -> Infos)
`Security/Subsections/Infos.vue`
```vue
<template>
    <h1>Infos for {{structure}}</h1>
</template>

<script>
    import { mapGetters } from "vuex";
  export default {
    name: "Infos",
    computed: {
      ...mapGetters([
        "currentStoredRoute"
      ]),
      structure() {
        // Use store getters to bind url parameters change
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