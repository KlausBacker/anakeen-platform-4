<template>
  <div>
    <nav v-if="isDockCollapsed || isDockExpanded">
      <span>Smart Elements</span>
    </nav>
    <div v-else-if="isHubContent" class="dev-smart-elements">
      <dev-smart-elements
        @navigate="onNavigate"
        :smartElement="element"
      ></dev-smart-elements>
    </div>
  </div>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import { setupVue } from "../../setup.js";
import elementsStore from "./storeModule.js";
export default {
  name: "ank-dev-elements",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "dev-smart-elements": () =>
      new Promise(resolve => {
        import("../../sections/SmartElements/SmartElements.vue").then(
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
    element() {
      return this.$store.getters["smartElements/element"];
    }
  },
  created() {
    if (this.isHubContent) {
      setupVue(this);
      if (this.$store) {
        this.$store.registerModule(["smartElements"], elementsStore);
      }
      const pattern = `/${this.entryOptions.route}(?:/(\\w+)(?:/(\\w+))?)?`;
      this.getRouter()
        .on(new RegExp(pattern), (...params) => {
          const elementId = params[0];
          const elementSection = params[1];
          if (elementId && elementSection) {
            let merge = {};
            let filterValue = null;
            switch (elementSection) {
              case "view":
                merge.props = {
                  initid: elementId,
                  viewId: "!defaultConsultation"
                };
                break;
              case "element":
                filterValue = window.location.search.match(
                  /formatType=(xml|json)/
                );
                if (filterValue && filterValue.length > 1) {
                  merge.props = {
                    elementId,
                    formatType: filterValue[1]
                  };
                }
                merge.component = "element-raw";
                break;
              case "properties":
                merge.props = {
                  elementId: elementId
                };
                break;
              case "security":
                filterValue = window.location.search.match(
                  /profileId=([\w_]+)/
                );
                if (filterValue && filterValue.length > 1) {
                  merge.props = {
                    profileId: filterValue[1],
                    detachable: true
                  };
                }
                break;
            }
            this.$store.commit(
              "smartElements/SET_ELEMENT",
              Object.assign(
                {},
                {
                  url: `${elementId}/${elementSection}`,
                  component: `element-${elementSection}`,
                  name: elementId,
                  label: elementId
                },
                merge
              )
            );
          }
        })
        .resolve();
    }
  },
  methods: {
    onNavigate(route) {
      const routeUrl =
        `/${this.entryOptions.route}/` +
        route
          .map(r => r.url)
          .join("/")
          .replace(/\/\//g, "/");
      this.getRouter().navigate(routeUrl);
    }
  }
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
