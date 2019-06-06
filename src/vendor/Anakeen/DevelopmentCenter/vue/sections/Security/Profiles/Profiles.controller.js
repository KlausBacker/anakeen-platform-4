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
  beforeRouteEnter(to, from, next) {
    const filterAction = vueInstance => () => {
      const filter = to.query;
      if (filter) {
        const filterObject = { logic: "and", filters: [] };
        filterObject.filters = Object.entries(filter).map(entry => {
          return {
            field: entry[0],
            operator: "contains",
            value: entry[1]
          };
        });
        if (filterObject.filters.length) {
          vueInstance.$refs.profilesGrid.dataSource.filter(filterObject);
        }
      }
    };
    if (to.name === "Security::Profile::Access::Element") {
      next(vueInstance => {
        vueInstance.$refs.profileSplitter.disableEmptyContent();
        if (vueInstance.$refs.profilesGrid.kendoGrid) {
          filterAction(vueInstance)();
        } else {
          vueInstance.$refs.profilesGrid.$once(
            "grid-ready",
            filterAction(vueInstance)
          );
        }
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    } else {
      next(vueInstance => {
        if (vueInstance.$refs.profilesGrid.kendoGrid) {
          filterAction(vueInstance)();
        } else {
          vueInstance.$refs.profilesGrid.$once(
            "grid-ready",
            filterAction(vueInstance)
          );
        }
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    }
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
    // const bindFilter = grid => {
    //   grid.bind("filter", event => {
    //     const filter = event.filter ? event.filter.filters[0] || null : null;
    //     if (filter) {
    //       this.$router.addQueryParams({
    //         [filter.field]: filter.value
    //       });
    //     } else {
    //       const query = Object.assign({}, this.$route.query);
    //       delete query[event.field];
    //       this.$router.push({ query: query });
    //     }
    //   });
    // };
    // if (this.$refs.profilesGrid.kendoGrid) {
    //   bindFilter(this.$refs.profilesGrid.kendoGrid);
    // } else {
    //   this.$refs.profilesGrid.$once("grid-ready", () => {
    //     bindFilter(this.$refs.profilesGrid.kendoGrid);
    //   });
    // }
  },
  methods: {
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
              }/view?filters=${this.$.param({
                id: event.data.rowData.id
              })}">${event.data.cellRender.html()}</a>`
            );
            break;
        }
      }
    },
    getRoute() {
      if (this.selectedProfile) {
        return Promise.resolve([
          {
            url: this.selectedProfile,
            name: this.selectedProfile,
            label: this.selectedProfile
          }
        ]);
      }
      return Promise.resolve([]);
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
