<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>Smart Elements</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-smart-elements">
            <dev-smart-elements></dev-smart-elements>
        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  import { setupVue, syncRouter } from "../../setup.js";
  import elementsStore from "./storeModule.js";
  export default {
    name: "ank-dev-elements",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "dev-smart-elements": () =>
        new Promise(resolve => {
          import("../../sections/SmartElements/SmartElements.vue").then(Component => {
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
          this.$store.registerModule(["smartElements"], elementsStore);
        }
        syncRouter(this);
      }
    },
  };
</script>
<style>
    .dev-smart-elements {
        height: 100%;
        width: 100%;
        flex: 1;
        display: flex;
        min-height: 0;
    }
</style>
