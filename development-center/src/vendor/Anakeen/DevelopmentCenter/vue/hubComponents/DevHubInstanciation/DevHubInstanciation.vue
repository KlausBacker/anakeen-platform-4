<template>
  <div>
    <nav v-if="isDockCollapsed || isDockExpanded">
      <span>Hub</span>
    </nav>
    <div v-else-if="isHubContent" class="dev-hub-instanciation">
      <dev-hub-instanciation></dev-hub-instanciation>
    </div>
  </div>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import { setupVue } from "../../setup.js";

export default {
  name: "ank-dev-hub-instanciation",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "dev-hub-instanciation": () =>
      new Promise(resolve => {
        import("../../sections/Hub/HubAdminInstanciation/HubAdminInstanciation.vue").then(
          Component => {
            resolve(Component.default);
          }
        );
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
    }
  },
};
</script>
<style>
.dev-hub-instanciation {
  height: 100%;
  width: 100%;
}
</style>
