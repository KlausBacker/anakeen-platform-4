import VendorSelector from "../../components/VendorSelector/VendorSelector.vue";

export default {
  components: {
    VendorSelector
  },
  data() {
    return {
      vendorName: "Anakeen",
      appTitle: "Development Center"
    };
  },
  computed: {
    routesSections() {
      return this.$route.matched;
    }
  },
  mounted() {},
  methods: {
    getRouteLabel(route) {
      if (route.meta && route.meta.label) {
        let title = route.name;
        if (typeof route.meta.label === "function") {
          title = route.meta.label.call(null, this.$route);
          if (!title) {
            return route.name;
          }
        } else {
          title = route.meta.label.trim();
        }
        const regex = /:[a-zA-Z0-9]+/g;
        const matches = title.match(regex) || [];
        matches.forEach(m => {
          const paramName = m.replace(":", "");
          title = title.replace(m, this.$route.params[paramName]);
        });
        return title;
      }
      return route.name;
    }
  }
};
