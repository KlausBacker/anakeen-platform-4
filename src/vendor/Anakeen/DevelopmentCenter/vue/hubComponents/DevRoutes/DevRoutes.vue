<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>Routes</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-routes">
            <dev-routes
            ></dev-routes>

        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  import { setupVue, syncRouter } from "../../setup.js";
  import routeStore from "./storeModule.js";
  export default {
    name: "ank-dev-routes",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "dev-routes": () =>
        new Promise(resolve => {
          import("../../sections/Routes/RoutesParent.vue").then(Component => {
            resolve(Component.default);
          });
        })
    },
    beforeCreate() {
      if (this.$options.propsData.displayType === "COLLAPSED") {
        this.$parent.$parent.collapsable = false;
        this.$parent.$parent.collapsed = false;
      }
    },
    created() {
      if (this.isHubContent) {
        setupVue(this);
        if (this.$store) {
          this.$store.registerModule(["routes"], routeStore);
        }
        syncRouter(this);
      }
    }
  };
</script>
<style>
    .dev-routes {
        flex: 1;
        display: flex;
        min-height: 0;
        height: 100%;
        width: 100%;
    }
</style>
