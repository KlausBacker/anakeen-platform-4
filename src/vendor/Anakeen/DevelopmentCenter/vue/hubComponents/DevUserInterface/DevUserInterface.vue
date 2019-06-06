<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>User Interface</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-user-interface">
            <dev-user-interface
                    @navigate="onNavigate"
                    :ssName="ssName"
                    :uiSection="uiSection"
                    :mask="mask"
            ></dev-user-interface>
        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  import { setupVue, syncRouter } from "../../setup.js";
  import uiStore from "./storeModule.js";
  export default {
    name: "ank-dev-user-interface",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "dev-user-interface": () =>
        new Promise(resolve => {
          import("../../sections/Ui/Ui.vue").then(Component => {
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
    computed: {
      ssName() {
        return this.$store.getters["userInterface/ssName"];
      },
      uiSection() {
        return this.$store.getters["userInterface/uiSection"];
      },
      mask() {
        return this.$store.getters["userInterface/mask"];
      }
    },
    created() {
      if (this.isHubContent) {
        setupVue(this);
        if (this.$store) {
          this.$store.registerModule(["userInterface"], uiStore);
        }
        const pattern = `/${this.entryOptions.route}(?:/(\\w+)(?:/(\\w+)(?:/(\\w+))?)?)?`;
        this.getRouter().on(new RegExp(pattern), (...params) => {
          const ssName = params[0];
          const uiSection = params[1];
          this.$store.dispatch("userInterface/setStructureName", ssName);
          this.$store.dispatch("userInterface/setUiSection", uiSection);
          switch (uiSection) {
            case "masks":
              if (params[2]) {
                this.$store.dispatch("userInterface/setMask", params[2]);
              }
              break;
          }
        }).resolve();
      }
    },
    methods: {
      onNavigate(route) {
        const routeUrl = `/${this.entryOptions.route}/`+route.map(r => r.url).join("/").replace(/\/\//g, '/');
        this.getRouter().navigate(routeUrl);
      }
    }
  };
</script>
<style>
    .dev-user-interface {
        flex: 1;
        display: flex;
        min-height: 0;
        height: 100%;
        width: 100%;
    }
</style>
