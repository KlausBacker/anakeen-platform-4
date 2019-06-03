<template>
    <div class="all-routes-tabs">
        <router-tabs :tabs="tabs">
            <template v-slot="slotProps">
                <component :is="slotProps.tab.component"></component>
            </template>
        </router-tabs>
    </div>
</template>

<script>
  import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
  export default {
    name: "allRoutesContent",
    components: {
      "routes": resolve => import("../Routes/Routes/Routes.vue").then((module) => resolve(module.default)),
      "middlewares": resolve => import("../Routes/Middlewares/Middlewares.vue").then((module) => resolve(module.default)),
      RouterTabs
    },
    data() {
      return {
        selected: "routes",
        contentVisible: false,
        tabs: [
          {
            name: "routes",
            label: "Routes",
            component: "routes"
          },
          {
            name: "middlewares",
            label: "Middlewares",
            component: "middlewares"
          }
        ]
      }
    }
  }
</script>

<style lang="scss" scoped>
    .all-routes-tabs {
        height: 100%;
        width: 100%;
        min-height: 0;
        display: flex;
    }
    .all-routes-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;

        .all-routes-content {
            min-height: 0;
            display: flex;
            flex: 1;
            padding: 1rem;
            border: 1px solid rgba(33, 37, 41, .125);
            border-radius: .25rem;
        }
    }
</style>