<template>
  <div class="security-parent">
    <router-tabs ref="tabsComponent" @tab-selected="onTabSelected" :tabs="tabs">
      <template v-slot="slotProps">
        <component :ref="slotProps.tab.name" :is="slotProps.tab.component" @navigate="onChildNavigate" @hook:mounted="onComponentMounted(slotProps.tab.name)" :ssName="ssName" :ssSection="ssSection" :wflName="wflName" :wflSection="wflSection" :routeAccess="routeAccess"></component>
      </template>
    </router-tabs>
  </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
@import "./SecurityDevCenter.scss";
</style>

<script>
import RouterTabs from "devComponents/RouterTabs/RouterTabs.vue";
export default {
  components: {
    RouterTabs,
    "security-structures": resolve =>
      import("./SmartStructures/SmartStructuresSecurity.vue").then(module =>
        resolve(module.default)
      ),
    "security-profiles": resolve =>
      import("./Profiles/Profiles.vue").then(module =>
        resolve(module.default)
      ),
    "security-fieldaccess": resolve =>
      import("./FieldAccess/FieldAccess.vue").then(module =>
        resolve(module.default)
      ),
    "security-roles": resolve =>
      import("./Role/RoleDevCenter.vue").then(module =>
        resolve(module.default)
      ),
    "security-workflows": resolve =>
      import("./Workflows/Workflows.vue").then(module =>
        resolve(module.default)
      ),
    "security-routes": resolve =>
      import("./Routes/RoutesDevCenter.vue").then(module =>
        resolve(module.default)
      )
  },
  props: ["securitySection", "ssName", "ssSection", "wflName", "wflSection", "routeAccess"],
  data() {
    return {
      subComponentsRefs: {},
      tabs: [
        {
          name: "Security::SmartStructures",
          label: "Smart Structures",
          component: "security-structures",
          url: "smartStructures"
        },
        {
          name: "Security::Profiles",
          label: "Profiles",
          component: "security-profiles",
          url: "profiles"
        },
        {
          name: "Security::FieldAccess",
          label: "Field Access",
          component: "security-fieldaccess",
          url: "fieldAccess"
        },
        {
          name: "Security::Roles",
          label: "Roles",
          component: "security-roles",
          url: "roles"
        },
        {
          name: "Security::Workflows",
          label: "Workflows",
          component: "security-workflows",
          url: "workflows"
        },
        {
          name: "Security::Routes",
          label: "Routes",
          component: "security-routes",
          url: "routes"
        }
      ]
    };
  },
  mounted() {
    this.$refs.tabsComponent.setSelectedTab((tab) => {
      return tab.url === this.securitySection;
    });
  },
  methods: {
    onComponentMounted(tabName) {
      this.$emit(`${tabName}-ready`);
    },
    onTabSelected() {
      this.onChildNavigate();
    },
    getRoute() {
      const selectedTab = this.$refs.tabsComponent.selectedTab;

      const result = [selectedTab];
      const ref = selectedTab.name;

      if (this.$refs[ref]) {
        this.subComponentsRefs[ref] = Promise.resolve(this.$refs[ref]);
      } else {
        this.subComponentsRefs[ref] = new Promise(resolve => {
          this.$once(`${ref}-ready`, () => {
            resolve(this.$refs[ref]);
          })
        });
      }
      return this.subComponentsRefs[ref].then((component) => {
        if (component && component.getRoute) {
          return component.getRoute().then((childRoute) =>  {
            result.push(...childRoute);
            return result;
          });
        } else {
          return result;
        }
      });
    },
    onChildNavigate() {
      this.getRoute().then((route) => {
        this.$emit("navigate", route);
      });
    }
  }
};
</script>
