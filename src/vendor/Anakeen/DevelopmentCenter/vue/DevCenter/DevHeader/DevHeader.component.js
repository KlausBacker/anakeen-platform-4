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
        const title = route.meta.label.trim();
        const indexOf = title.indexOf(":");
        if (indexOf === 0 && title.length > 0) {
          const paramName = title.substring(1).trim();
          return this.$route.params[paramName];
        } else {
          return title;
        }
      }
      return route.name;
    }
  }
};
