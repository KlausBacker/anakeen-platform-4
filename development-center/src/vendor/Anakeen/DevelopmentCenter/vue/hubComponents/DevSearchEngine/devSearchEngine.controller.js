import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import { setupVue } from "../../setup";
export default {
  name: "ank-dev-search",
  extends: HubElement,
  components: {
    "dev-search-engine-panel": () =>
      new Promise(resolve => {
        import("../../sections/SearchEngine/DevSearchEnginePanel").then(
          Component => {
            resolve(Component.default);
          }
        );
      })
  },
  created() {
    this.subRouting();
  },
  data() {
    return {
      filters: {
        inputvalue: "",
        fromname: "",
        id: "",
        name: "",
        title: ""
      },
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      subRouting: () => {
        const url = (this.routeUrl() + ".*").replace(/\/\/+/g, "/");
        this.registerRoute(new RegExp(url), () => {
          this.updateFiltersFromUrl();
        }).resolve(window.location.pathname);
      }
    };
  },
  methods: {
    onButtonSearchClick() {
      this.$nextTick(() => {
        this.$_hubEventBus.emit("searchEngineClick");
      });
    },
    updateFiltersFromUrl() {
      const inputMatch = window.location.search.match(/inputvalue=(\w+)/);
      const nameMatch = window.location.search.match(/\Wname=(\w+)/);
      const fromnameMatch = window.location.search.match(/fromname=(\w+)/);
      const idMatch = window.location.search.match(/id=(\d+)/);
      const titleMatch = window.location.search.match(/title=(\w+)/);

      if (inputMatch) {
        this.filters.inputvalue = inputMatch[1];
      }
      if (nameMatch) {
        this.filters.name = nameMatch[1];
      }
      if (fromnameMatch) {
        this.filters.fromname = fromnameMatch[1];
      }
      if (idMatch) {
        this.filters.id = idMatch[1];
      }
      if (titleMatch) {
        this.filters.title = titleMatch[1];
      }
    },
    getFiltersUrl: function() {
      const filterStrings = [];
      if (this.filters.inputvalue) {
        filterStrings.push("inputvalue=" + this.filters.inputvalue);
      }
      if (this.filters.fromname) {
        filterStrings.push("fromname=" + this.filters.fromname);
      }
      if (this.filters.id) {
        filterStrings.push("id=" + this.filters.id);
      }
      if (this.filters.name) {
        filterStrings.push("name=" + this.filters.name);
      }
      if (this.filters.title) {
        filterStrings.push("title=" + this.filters.title);
      }

      let url = "";

      if (filterStrings.length > 0) {
        url += "?";
        url += filterStrings.join("&");
      }

      return url;
    }
  },
  mounted() {
    setupVue(this);
  },
  watch: {
    filters: {
      handler: function() {
        const newUrl = this.routeUrl() + this.getFiltersUrl();
        this.navigate(newUrl);
      },
      deep: true
    }
  }
};
