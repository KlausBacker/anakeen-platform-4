<template>
    <div>
        <nav v-if="isDockCollapsed || isDockExpanded">
            <span>Routes</span>
        </nav>
        <div v-else-if="isHubContent" class="dev-routes">
            <dev-routes
                    @navigate="onNavigate"
                    :routeSection="routeSection"
                    :routeFilter="routeFilter"
                    :middlewareFilter="middlewareFilter"
            ></dev-routes>

        </div>
    </div>
</template>
<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
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
      },
      routeFilter() {
        return this.$store.getters["routes/routeFilter"];
      },
      middlewareFilter() {
        return this.$store.getters["routes/middlewareFilter"];
      }
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
          this.$store.registerModule(["routes"], routeStore);
        }
        const pattern = `(?:/${this.entryOptions.route}(?:/(\\w+))?)(.*)`;
        this.getRouter().on(new RegExp(pattern), (...params) => {
          const routeSection = params[0];
          this.$store.commit("routes/SET_ROUTE_SECTION", routeSection);
          const queries = window.location.search.replace(/^\?/, "").split("&");
          const filters = queries.reduce((acc, curr) => {
            const entry = curr.split("=");
            acc[entry[0]] = entry[1];
            return acc;
          }, {});
          switch (routeSection) {
            case "routes":
              this.$store.commit("routes/SET_ROUTE_FILTER", filters);
              break;
            case "middlewares":
              this.$store.commit("routes/SET_MIDDLEWARE_FILTER", filters);
              break;
          }
        }).resolve();
      }
    },
    methods: {
      onNavigate(route, filter) {
        const routeUrl = `/${this.entryOptions.route}/`+route.map(r => r.url).join("/").replace(/\/\//g, '/');
        let search = "";
        if (filter && Object.keys(filter).length) {
          search = `?${kendo.jQuery.param(filter)}`;
        }
        this.getRouter().navigate(`${routeUrl}/${search}`);
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
