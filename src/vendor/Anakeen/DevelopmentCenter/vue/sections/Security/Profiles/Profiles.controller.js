import Vue from "vue";
import Splitter from "../../../components/Splitter/Splitter.vue";
import { AnkSEGrid } from "@anakeen/ank-components";

Vue.use(Splitter);
Vue.use(AnkSEGrid);
export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter
  },
  data() {
    return {
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
    const filterAction = vueInstance => {
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
          filterAction(vueInstance);
        } else {
          vueInstance.$refs.profilesGrid.$once("grid-ready", filterAction);
        }
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    } else {
      next(vueInstance => {
        if (vueInstance.$refs.profilesGrid.kendoGrid) {
          filterAction(vueInstance);
        } else {
          vueInstance.$refs.profilesGrid.$once("grid-ready", filterAction);
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
    const bindFilter = grid => {
      grid.bind("filter", event => {
        const filter = event.filter ? event.filter.filters[0] || null : null;
        if (filter) {
          this.$router.addQueryParams({
            [filter.field]: filter.value
          });
        } else {
          const query = Object.assign({}, this.$route.query);
          delete query[event.field];
          this.$router.push({ query: query });
        }
      });
    };
    if (this.$refs.profilesGrid.kendoGrid) {
      bindFilter(this.$refs.profilesGrid.kendoGrid);
    } else {
      this.$refs.profilesGrid.$once("grid-ready", () => {
        bindFilter(this.$refs.profilesGrid.kendoGrid);
      });
    }
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
              }/view?id=${
                event.data.rowData.id
              }">${event.data.cellRender.html()}</a>`
            );
            break;
        }
      }
    },
    actionClick(event) {
      switch (event.data.type) {
        case "view": {
          this.$router.push({
            name: "Security::Profile::Access::Element",
            params: {
              seIdentifier: event.data.row.name || event.data.row.initid
            }
          });
          this.$refs.profileSplitter.disableEmptyContent();
          break;
        }
      }
    }
  }
};
