<template>
  <hub-element-layout>
    <nav>
      <span>Routes</span>
    </nav>
    <template v-slot:hubContent>
      <div class="dev-routes">
        <dev-routes
          @navigate="onNavigate"
          :routeSection="routeSection"
        ></dev-routes>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
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
  computed: {
    routeSection() {
      return this.$store.getters["routes/routeSection"];
    }
  },
  beforeCreate() {
    this.$parent.$parent.collapsable = false;
    this.$parent.$parent.collapsed = false;
  },
  created() {
    setupVue(this);
    if (this.$store) {
      this.$store.registerModule(["routes"], routeStore);
    }
    const pattern = `/${this.entryOptions.route}(?:/(\\w+))?`;
    this.getRouter()
      .on(new RegExp(pattern), (...params) => {
        const routeSection = params[0];
        this.$store.commit("routes/SET_ROUTE_SECTION", routeSection);
      })
      .resolve();
  },
  methods: {
    onNavigate(route, filter) {
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
.dev-routes {
  flex: 1;
  display: flex;
  min-height: 0;
  height: 100%;
  width: 100%;
}
</style>
