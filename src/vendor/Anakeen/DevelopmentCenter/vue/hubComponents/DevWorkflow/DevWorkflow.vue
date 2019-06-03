<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>Workflow</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-workflow">
            <dev-workflow
            ></dev-workflow>

        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  import { setupVue, syncRouter } from "../../setup.js";
  import workflowStore from "./storeModule.js";
  export default {
    name: "ank-dev-workflow",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "dev-workflow": () =>
        new Promise(resolve => {
          import("../../sections/Workflow/Workflow.vue").then(Component => {
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
          this.$store.registerModule(["workflow"], workflowStore);
        }
        syncRouter(this);
      }
    },
  };
</script>
<style>
    .dev-workflow {
        flex: 1;
        display: flex;
        min-height: 0;
        height: 100%;
        width: 100%;
    }
</style>
