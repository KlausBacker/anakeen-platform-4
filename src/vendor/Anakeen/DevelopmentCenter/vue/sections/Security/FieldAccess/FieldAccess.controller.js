import Vue from "vue";
import Splitter from "devComponents/Splitter/Splitter.vue";
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
    if (to.name === "Security::FieldAccess::Access") {
      next(vueInstance => {
        vueInstance.$refs.fallSplitter.disableEmptyContent();
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    } else {
      next(vueInstance => {
        const filterAction = () => {
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
              vueInstance.$refs.fallGrid.dataSource.filter(filterObject);
            }
          }
        };
        if (vueInstance.$refs.fallGrid.kendoGrid) {
          filterAction();
        } else {
          vueInstance.$refs.fallGrid.$once("grid-ready", filterAction);
        }
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    }
  },
  devCenterRefreshData() {
    if (this.$refs.fallGrid && this.$refs.fallGrid.dataSource) {
      this.$refs.fallGrid.dataSource.read();
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
    if (this.$refs.fallGrid.kendoGrid) {
      bindFilter(this.$refs.fallGrid.kendoGrid);
    } else {
      this.$refs.fallGrid.$once("grid-ready", () => {
        bindFilter(this.$refs.fallGrid.kendoGrid);
      });
    }
  },
  methods: {
    cellRender(event) {
      if (event.data && event.data.columnConfig) {
        switch (event.data.columnConfig.field) {
          case "dpdoc_famid":
          case "fall_famid":
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
    actionClick(event) {
      switch (event.data.type) {
        case "rights": {
          this.$router.push({
            name: "Security::FieldAccess::Access",
            params: {
              fallIdentifier: event.data.row.name || event.data.row.initid
            }
          });
          this.$refs.fallSplitter.disableEmptyContent();
          break;
        }
      }
    }
  }
};
