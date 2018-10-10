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
  name: "parameters", // name of the route (recommended for nested routing)
  path: "parameters", // path of the route (required) (relative to parent),
  component: Parameters, // The Vue component to route to (optional, if not present the component display is the parent route component)
  children: [ // Eventually, some sub routes of the component
    {
      name: "General",
      path: "general",
      component: General
    },
    {
      name: "SmartStructure",
      path: "smartstructure",
      component: SSList,
      children: {
        path: ":ssname" // Url params are defined with ":paramname"
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