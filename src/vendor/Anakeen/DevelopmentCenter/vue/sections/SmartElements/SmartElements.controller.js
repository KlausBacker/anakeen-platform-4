import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import PropertiesView from "devComponents/PropertiesView/PropertiesView.vue";
import ElementView from "./ElementView/ElementView.vue";
import RawElementView from "./RawElementView/RawElementView.vue";
import ProfileGrid from "../../components/profile/profile.vue";

const parseFilters = filters => {
  const result = {};
  if (filters) {
    filters.split("&").forEach(filter => {
      const entry = filter.split("=");
      if (entry && entry.length) {
        const key = entry[0];
        const value = entry[1];
        result[key] = value;
      }
    });
    return result;
  } else {
    return null;
  }
};

const filterAction = (to, vueInstance) => () => {
  let filter = to.query ? parseFilters(to.query.filters) || null : null;
  if (filter) {
    const filterObject = { logic: "and", filters: [] };
    filterObject.filters = Object.entries(filter).map(entry => {
      const filterOperator = entry[0] === "initid" ? "eq" : "contains";
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
    "ank-splitter": Splitter,
    "element-view": ElementView,
    "element-properties": PropertiesView,
    "element-security": ProfileGrid,
    "element-raw": RawElementView
  },
  props: ["smartElement"],
  watch: {
    smartElement(newValue) {
      this.$refs.splitter.disableEmptyContent();
      this.selectedElement = newValue;
    }
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
        if (vueInstance.$refs.grid.kendoGrid) {
          filterAction(to, vueInstance)();
        } else {
          vueInstance.$refs.grid.$once(
            "grid-ready",
            filterAction(to, vueInstance)
          );
        }
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    } else {
      next(vueInstance => {
        if (vueInstance.$refs.grid.kendoGrid) {
          filterAction(to, vueInstance)();
        } else {
          vueInstance.$refs.grid.$once(
            "grid-ready",
            filterAction(to, vueInstance)
          );
        }
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    }
  },
  beforeRouteUpdate(to, from, next) {
    if (this.$refs.grid.kendoGrid) {
      filterAction(to, this)();
    } else {
      this.$refs.grid.$once("grid-ready", filterAction(to, this));
    }
    next();
  },
  data() {
    return {
      selectedElement: this.smartElement,
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
    if (this.selectedElement) {
      this.$refs.splitter.disableEmptyContent();
    }
    const bindFilter = grid => {
      grid.bind("filter", event => {
        const filter = event.filter ? event.filter.filters[0] || null : null;
        if (filter) {
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
        } else {
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
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
    getFilter(nextFilter) {
      let result = {};
      if (this.$refs.grid) {
        const filter = this.$refs.grid.dataSource.filter();
        if (filter && filter.filters) {
          filter.filters.forEach(f => {
            result[f.field] = f.value;
          });
        }
      }
      if (nextFilter) {
        result = Object.assign({}, result, {
          [nextFilter.field]: nextFilter.value
        });
      }
      return $.param(result);
    },
    getRoute() {
      if (this.selectedElement) {
        return Promise.resolve([this.selectedElement]);
      }
      return Promise.resolve([]);
    },
    actionClick(event) {
      let seIdentifier;
      switch (event.data.type) {
        case "consult":
          seIdentifier = event.data.row.name || event.data.row.initid;
          this.$refs.splitter.disableEmptyContent();
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/view`,
            component: "element-view",
            props: {
              initid: seIdentifier,
              viewId: "!defaultConsultation"
            },
            name: seIdentifier,
            label: seIdentifier
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
        case "viewJSON":
          seIdentifier = event.data.row.name || event.data.row.initid;
          this.$refs.splitter.disableEmptyContent();
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/element?formatType=json`,
            component: "element-raw",
            props: {
              elementId: seIdentifier,
              elementType: docTypeString(event.data.row.doctype),
              formatType: "json"
            },
            name: seIdentifier,
            label: seIdentifier
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
        case "viewXML":
          seIdentifier = event.data.row.name || event.data.row.initid;
          this.$refs.splitter.disableEmptyContent();
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/element?formatType=xml`,
            component: "element-raw",
            props: {
              elementId: seIdentifier,
              elementType: docTypeString(event.data.row.doctype),
              formatType: "xml"
            },
            name: seIdentifier,
            label: seIdentifier
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
        case "viewProps":
          seIdentifier = event.data.row.name || event.data.row.initid;
          this.$refs.splitter.disableEmptyContent();
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/properties`,
            component: "element-properties",
            props: {
              elementId: seIdentifier
            },
            name: seIdentifier,
            label: seIdentifier
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
        case "security":
          if (event.data.row.profid) {
            seIdentifier = event.data.row.name || event.data.row.initid;
            this.$refs.splitter.disableEmptyContent();
            event.preventDefault();
            this.selectedElement = {
              url: `${seIdentifier}/security?profileId=${
                event.data.row.profid
              }`,
              component: "element-security",
              props: {
                profileId: event.data.row.profid,
                detachable: true
              },
              name: seIdentifier,
              label: seIdentifier
            };
            this.getRoute().then(route => {
              this.$emit("navigate", route);
            });
          }
          break;
      }
    }
  }
};
