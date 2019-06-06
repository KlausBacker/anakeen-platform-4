<template>
    <div class="all-routes-tabs">
        <router-tabs ref="tabsComponent" :tabs="tabs" @tab-selected="onTabSelected">
            <template v-slot="slotProps">
                <component :is="slotProps.tab.component" :ref="slotProps.tab.name" :routeFilter="routeFilter" :middlewareFilter="middlewareFilter" @filter="onFilter"></component>
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
    props: ["routeSection", "routeFilter", "middlewareFilter"],
    watch: {
      routeSection(newValue) {
        this.$refs.tabsComponent.setSelectedTab((tab) => {
          return tab.url === newValue;
        })
      }
    },
    mounted() {
      this.$refs.tabsComponent.setSelectedTab((tab) => {
        return tab.url === this.routeSection;
      })
    },
    data() {
      return {
        selected: "routes",
        contentVisible: false,
        tabs: [
          {
            name: "routes",
            label: "Routes",
            component: "routes",
            url: "routes"
          },
          {
            name: "middlewares",
            label: "Middlewares",
            component: "middlewares",
            url: "middlewares"
          }
        ]
      }
    },
    methods: {
      onTabSelected() {
        this.onChildNavigate();
      },
      getRoute() {
        return Promise.resolve([this.$refs.tabsComponent.selectedTab]);
      },
      onChildNavigate() {
        this.getRoute().then((route) => {
          this.$emit("navigate", route);
        });
      },
      onFilter(filter) {
        this.getRoute().then((route) => {
          this.$emit("navigate", route, filter);
        })
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