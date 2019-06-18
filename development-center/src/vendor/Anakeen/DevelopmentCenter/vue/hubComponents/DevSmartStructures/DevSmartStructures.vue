<template>
  <div>
    <nav v-if="isDockCollapsed || isDockExpanded">
      <span>Smart Structures</span>
    </nav>
    <div v-else-if="isHubContent" class="dev-smart-structures">
      <dev-smart-structures
              @navigate="onNavigate"
              :ssName="ssName"
              :ssType="ssType"
              :ssDetails="ssDetails"
      ></dev-smart-structures>
    </div>
  </div>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import { setupVue, syncRouter } from "../../setup.js";
import structureStore from "./storeModule.js";
export default {
  name: "ank-dev-structures",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "dev-smart-structures": () =>
      new Promise(resolve => {
        import("../../sections/SmartStructures/SmartStructures.vue").then(
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
  computed: {
    ssName() {
      return this.$store.getters["smartStructures/ssName"];
    },
    ssType() {
      return this.$store.getters["smartStructures/ssType"];
    },
    ssDetails() {
      return this.$store.getters["smartStructures/ssDetails"];
    },
  },
  created() {
    if (this.isHubContent) {
      setupVue(this);
      if (this.$store) {
        this.$store.registerModule(["smartStructures"], structureStore);
      }
      const pattern = `/${this.entryOptions.route}(?:/(\\w+)(?:/(\\w+)(?:/(\\w+))?)?)?`;
      this.getRouter().on(new RegExp(pattern), (...params) => {
       const ssName = params[0];
       const ssType = params[1];
       const ssDetails = params[2];
       this.$store.dispatch("smartStructures/setStructureName", ssName);
       this.$store.dispatch("smartStructures/setStructureType", ssType);
       this.$store.dispatch("smartStructures/setStructureDetails", ssDetails);
      }).resolve();
    }
  },
  methods: {
    onNavigate(route) {
      const routeUrl = `/devel/${this.entryOptions.route}/`+route.map(r => r.url).join("/").replace(/\/\//g, '/');
      this.navigate(routeUrl);
    }
  }
};
</script>
<style>
.dev-smart-structures {
  flex: 1;
  display: flex;
  min-height: 0;
  height: 100%;
  width: 100%;
}
</style>
