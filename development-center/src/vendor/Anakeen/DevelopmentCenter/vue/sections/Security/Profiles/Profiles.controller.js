import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import ProfileView from "./ProfileVisualizer/ProfileVisualizerContent.vue";
import { interceptDOMLinks } from "../../../setup";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "profile-view": ProfileView
  },
  props: ["profile"],
  watch: {
    profileId(newValue) {
      this.$refs.profileSplitter.disableEmptyContent();
      this.initFilters(window.location.search);
      this.selectedProfile = newValue;
    }
  },
  created() {
    interceptDOMLinks("body", path => {
      const baseUrl = path.split("?")[0];
      const params = path.split("?")[1];
      if (baseUrl === "/devel/security/profiles") {
        if (params) {
          this.initFilters(params);
        }
      }
    });
  },
  data() {
    return {
      selectedProfile: this.profile,
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        }
      ]
    };
  },
  devCenterRefreshData() {
    if (this.$refs.profilesGrid) {
      this.$refs.profilesGrid.refreshGrid(true);
    }
  },
  mounted() {
    if (this.selectedProfile) {
      this.$refs.profileSplitter.disableEmptyContent();
    }
    this.initFilters(window.location.search);
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(name|title|fromid|dpdoc_famid)=([^&]+)/g;
        let match;
        while ((match = re.exec(searchUrl))) {
          if (match && match.length >= 3) {
            const field = match[1];
            const value = decodeURIComponent(match[2]);
            this.$refs.profilesGrid.addFilter({ field, operator: "contains", value });
          }
        }
      };
      if (this.$refs.profilesGrid) {
        computeFilters();
      } else {
        this.$refs.profilesGrid.$once("gridReady", () => {
          computeFilters();
        });
      }
    },
    onGridDataBound() {
      this.getRoute().then(route => {
        this.$emit("navigate", route);
      });
    },
    getFilter() {
      if (this.$refs.profilesGrid && this.$refs.profilesGrid.kendoGrid) {
        const currentFilter = this.$refs.profilesGrid.kendoGrid.dataSource.filter();
        if (currentFilter) {
          const filters = currentFilter.filters;
          return filters.reduce((acc, curr) => {
            acc[curr.field] = curr.value;
            return acc;
          }, {});
        }
      }
      return {};
    },
    getRoute() {
      const filter = this.getFilter();
      const filterUrl = Object.keys(filter).length ? `?${$.param(filter)}` : "";
      if (this.selectedProfile) {
        return Promise.resolve([
          {
            url: this.selectedProfile + filterUrl,
            name: this.selectedProfile,
            label: this.selectedProfile
          }
        ]);
      }
      return Promise.resolve([{ url: filterUrl }]);
    },
    actionClick(event) {
      switch (event.data.type) {
        case "view":
          this.$refs.profileSplitter.disableEmptyContent();
          this.selectedProfile = event.data.row.properties.name || event.data.row.properties.id.toString();
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
      }
    }
  }
};
