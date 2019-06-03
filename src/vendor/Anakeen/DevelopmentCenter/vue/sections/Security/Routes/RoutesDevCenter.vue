<template>
    <div class="routes-parent">
<!--        <router-tabs :items="items"></router-tabs>-->
        <router-tabs ref="tabsComponent" @tab-selected="onTabSelected" :tabs="tabs">
            <template v-slot="slotProps">
                <component :is="slotProps.tab.component"></component>
            </template>
        </router-tabs>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./RoutesDevCenter.scss";
</style>

<script>
  import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
    export default {
      components: {
        RouterTabs,
        "routes-access": resolve =>
          import("./RoutesAcl/RoutesAcl.vue").then(module => resolve(module.default)),
        "routes-permissions": resolve =>
          import("./RoutesPermissions/RoutesPermissions.vue").then(module => resolve(module.default)),
      },
      props: ["routeAccess"],
      data() {
        return {
          tabs: [
            {
              name: "Security::Routes::RoutesAcl",
              label: "Access Control",
              component: "routes-access",
              url: "access"
            },
            {
              name: "Security::Routes::RoutesPermissions",
              label: "Permissions",
              component: "routes-permissions",
              url: "permissions"
            }
          ]
        }
      },
      mounted() {
        this.$refs.tabsComponent.setSelectedTab((tab) => {
          return tab.url === this.routeAccess;
        })
      },
      methods: {
        onTabSelected() {
          this.onChildNavigate();
        },
        onChildNavigate() {
          this.getRoute().then((route) => {
            this.$emit("navigate", route);
          });
        },
        getRoute() {
          return Promise.resolve([this.$refs.tabsComponent.selectedTab]);
        },
      }
    }
</script>