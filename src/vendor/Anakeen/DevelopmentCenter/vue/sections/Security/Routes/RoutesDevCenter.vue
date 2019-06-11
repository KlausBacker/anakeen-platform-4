<template>
    <div class="routes-parent">
<!--        <router-tabs :items="items"></router-tabs>-->
        <router-tabs ref="tabsComponent" @tab-selected="onTabSelected" :tabs="tabs">
            <template v-slot="slotProps">
                <component :is="slotProps.tab.component" :ref="slotProps.tab.name" @hook:mounted="onSubComponentMounted(slotProps.tab.name)" @navigate="onChildNavigate"></component>
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
      watch: {
        routeAccess(newValue) {
          this.$refs.tabsComponent.setSelectedTab((tab) => {
            return tab.url === newValue;
          })
        }
      },
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
        onSubComponentMounted(tabName) {
          this.$emit(`${tabName}-ready`);
        },
        getRoute() {
          const selectedTab = this.$refs.tabsComponent.selectedTab;

          const result = [selectedTab];
          const ref = selectedTab.name;
          let componentPromise;
          if (this.$refs[ref]) {
            componentPromise = Promise.resolve(this.$refs[ref]);
          } else {
            componentPromise = new Promise(resolve => {
              this.$once(`${ref}-ready`, () => {
                resolve(this.$refs[ref]);
              });
            });
          }
          return componentPromise.then(component => {
            if (component && component.getRoute) {
              return component.getRoute().then(childRoute => {
                result.push(...childRoute);
                return result;
              });
            } else {
              return result;
            }
          });
        },
      }
    }
</script>