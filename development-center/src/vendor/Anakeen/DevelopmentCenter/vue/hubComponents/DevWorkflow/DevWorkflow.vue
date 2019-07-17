<template>
  <hub-element-layout>
    <nav>
      <span>Workflow</span>
    </nav>
    <template v-slot:hubContent>
      <div class="dev-workflow">
        <dev-workflow
          @navigate="onNavigate"
          :wflName="wflName"
          :wflType="wflType"
        ></dev-workflow>
      </div>
    </template>
  </hub-element-layout>
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
  computed: {
    wflName() {
      return this.$store.getters["workflow/wflName"];
    },
    wflType() {
      return this.$store.getters["workflow/wflType"];
    }
  },
  beforeCreate() {
    this.$parent.$parent.collapsable = false;
    this.$parent.$parent.collapsed = false;
  },
  created() {
    setupVue(this);
    if (this.$store) {
      this.$store.registerModule(["workflow"], workflowStore);
    }
    const pattern = `/${this.entryOptions.route}(?:/(\\w+)(?:/(\\w+))?)?`;
    this.getRouter()
      .on(new RegExp(pattern), (...params) => {
        const wflName = params[0];
        const wflType = params[1];
        this.$store.dispatch("workflow/setWorkflowName", wflName);
        this.$store.dispatch("workflow/setWorkflowType", wflType);
      })
      .resolve();
  },
  methods: {
    onNavigate(route) {
      const routeUrl =
        `/devel/${this.entryOptions.route}/` +
        route
          .map(r => r.url)
          .join("/")
          .replace(/\/\//g, "/");
      this.navigate(routeUrl);
    }
  }
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
