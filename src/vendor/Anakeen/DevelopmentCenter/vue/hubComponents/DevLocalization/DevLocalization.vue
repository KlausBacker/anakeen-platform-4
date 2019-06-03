<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>Localization</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-localization">
            <dev-localization
            ></dev-localization>

        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  import { setupVue, syncRouter } from "../../setup.js";
  import localizationStore from "./storeModule.js";
  export default {
    name: "ank-dev-localization",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "dev-localization": () =>
        new Promise(resolve => {
          import("../../sections/Localization/Localization/Localization.vue").then(Component => {
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
          this.$store.registerModule(["localization"], localizationStore);
        }
        syncRouter(this);
      }
    }
  };
</script>
<style>
    .dev-localization {
        height:100%;
        min-height: 0;
        flex: 1;
        display: flex;
    }
</style>
