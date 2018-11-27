import Vue from "vue";
import Splitter from "../../components/Splitter/Splitter.vue";
import { AnkSEGrid } from "@anakeen/ank-components";

Vue.use(AnkSEGrid);
Vue.use(Splitter);

const docTypeString = doctype => {
  switch (doctype) {
    case "F":
      return "element";
    case "C":
      return "structure";
    case "D":
      return "folder";
    case "P":
      return "profil";
    case "S":
      return "search";
    case "T":
      return "temporary";
    case "W":
      return "workflow";
    case "Z":
      return "zombie";
    default:
      return "element";
  }
};

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter
  },
  computed: {
    urlConfig() {
      return `/api/v2/devel/security/elements/config/`;
    }
  },
  beforeRouteEnter(to, from, next) {
    if (to.name !== "SmartElements") {
      next(vueInstance => {
        vueInstance.$refs.splitter.disableEmptyContent();
      });
    } else {
      next(vueInstance => {
        const filterAction = () => {
          const filter = to.query;
          if (filter) {
            const filterObject = { logic: "and", filters: [] };
            filterObject.filters = Object.entries(filter).map(entry => {
              const filterOperator = entry[0] === "id" ? "eq" : "contains";
              return {
                field: entry[0],
                operator: filterOperator,
                value: entry[1]
              };
            });
            if (filterObject.filters.length) {
              vueInstance.$refs.grid.dataSource.filter(filterObject);
            }
          }
        };
        if (vueInstance.$refs.grid.kendoGrid) {
          filterAction();
        } else {
          vueInstance.$refs.grid.$once("grid-ready", filterAction);
        }
      });
    }
  },
  data() {
    return {
      splitterSmartElementEmpty: true,
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
      ],
      viewURL: "",
      viewType: "html",
      viewRawContent: "",
      viewComponent: null,
      viewComponentProps: {}
    };
  },
  devCenterRefreshData() {
    if (this.$refs.grid && this.$refs.grid.dataSource) {
      this.$refs.grid.dataSource.read();
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
    if (this.$refs.grid.kendoGrid) {
      bindFilter(this.$refs.grid.kendoGrid);
    } else {
      this.$refs.grid.$once("grid-ready", () => {
        bindFilter(this.$refs.grid.kendoGrid);
      });
    }
  },
  methods: {
    cellRender(event) {
      if (event.data) {
        if (event.data.columnConfig) {
          switch (event.data.columnConfig.field) {
            case "fromid":
              event.data.cellRender.html(`
                <a data-role="develRouterLink" href="/devel/smartStructures/${
                  event.data.cellData.name
                }/infos">${event.data.cellData.name}</a>
              `);
              break;
          }
        }
        if (event.data.rowData.doctype && event.data.rowData.doctype === "C") {
          event.data.cellRender.addClass("structure-type-cell");
        }
      }
    },
    actionClick(event) {
      switch (event.data.type) {
        case "consult":
          event.preventDefault();
          this.$router.push({
            name: "SmartElements::ElementView",
            params: { seIdentifier: event.data.row.name || event.data.row.id }
          });
          break;
        case "viewJSON":
          this.$router.push({
            name: "SmartElements::RawElementView",
            params: {
              seIdentifier: event.data.row.name || event.data.row.id,
              seType: docTypeString(event.data.row.doctype)
            },
            query: {
              formatType: "json"
            }
          });
          break;
        case "viewXML":
          this.$router.push({
            name: "SmartElements::RawElementView",
            params: {
              seIdentifier: event.data.row.name || event.data.row.id,
              seType: docTypeString(event.data.row.doctype)
            },
            query: {
              formatType: "xml"
            }
          });
          break;
        case "viewProps":
          this.$router.push({
            name: "SmartElements::PropertiesView",
            params: {
              seIdentifier: event.data.row.name || event.data.row.id
            }
          });
          break;
        case "security":
          if (event.data.row.profid) {
            this.$router.push({
              name: "SmartElements::ProfilView",
              params: {
                seIdentifier: event.data.row.name || event.data.row.id
              },
              query: {
                profileId: event.data.row.profid
              }
            });
          }
          break;
      }
      this.$refs.splitter.disableEmptyContent();
    }
  }
};
