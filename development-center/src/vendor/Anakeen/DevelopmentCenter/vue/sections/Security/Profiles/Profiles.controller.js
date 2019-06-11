import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import ProfileView from "./ProfileVisualizer/ProfileVisualizerContent.vue";

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
    if (this.$refs.profilesGrid && this.$refs.profilesGrid.dataSource) {
      this.$refs.profilesGrid.dataSource.read();
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
        const filters = [];
        while ((match = re.exec(searchUrl))) {
          if (match && match.length >= 3) {
            const field = match[1];
            const value = decodeURIComponent(match[2]);
            filters.push({
              field,
              operator: "contains",
              value
            });
          }
        }
        if (filters.length) {
          this.$refs.profilesGrid.dataSource.filter(filters);
        }
      };
      if (this.$refs.profilesGrid.kendoGrid) {
        computeFilters();
      } else {
        this.$refs.profilesGrid.$once("grid-ready", () => {
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
    cellRender(event) {
      if (event.data && event.data.columnConfig) {
        switch (event.data.columnConfig.field) {
          case "family":
            event.data.cellRender.text(event.data.cellData.name);
            break;
          case "fromid":
            event.data.cellRender.text(event.data.cellData.name);
            break;
          case "dpdoc_famid":
            if (event.data.cellData.name) {
              event.data.cellRender.html(
                `<a data-role="develRouterLink" href="/devel/smartStructures/${
                  event.data.cellData.name
                }/infos">${event.data.cellData.name}</a>`
              );
            } else {
              event.data.cellRender.text("");
            }
            break;
          case "title":
            event.data.cellRender.html(
              `<a data-role="develRouterLink" href="/devel/smartElements/${
                event.data.rowData.id
              }/view?initid=${
                event.data.rowData.id
              }">${event.data.cellRender.html()}</a>`
            );
            break;
        }
      }
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
          this.selectedProfile = event.data.row.name || event.data.row.id;
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
      }
    }
  }
};
