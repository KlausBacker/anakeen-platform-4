<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>Enumerates</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-enumerates">
            <dev-enumerates
                    @filter="onFilter"
            ></dev-enumerates>

        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  import { setupVue, syncRouter } from "../../setup.js";
  import enumStore from "./storeModule.js";
  import deparam from "jquery-deparam";

  export default {
    name: "ank-dev-enumerates",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "dev-enumerates": () =>
        new Promise(resolve => {
          import("../../sections/Enum/EnumDevCenter.vue").then(Component => {
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
          this.$store.registerModule(["enumerates"], enumStore);
        }
        console.log(this.entryOptions.route);
        const pattern = `(?:/${this.entryOptions.route}/(.*))`;
        this.getRouter().on("*", (...params) => {
          console.log(...params);
        });
      }
    },
    methods: {
      onFilter(filter) {
        this.getRouter().navigate(`/${this.entryOptions.route}/?${kendo.jQuery.param({ filters: filter})}`)
      }
    }
  };
</script>
<style>
    .dev-enumerates {
        height: 100%;
        width: 100%;
    }
</style>
