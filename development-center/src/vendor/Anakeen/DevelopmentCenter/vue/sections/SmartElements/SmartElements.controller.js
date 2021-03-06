import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import PropertiesView from "devComponents/PropertiesView/PropertiesView.vue";
import ElementView from "./ElementView/ElementView.vue";
import RawElementView from "./RawElementView/RawElementView.vue";
import ProfileGrid from "../../components/profile/profile.vue";
import LogicalNameForm from "../../components/LogicalName/LogicalName.vue";
import { interceptDOMLinks } from "../../setup";

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
    "element-logical-name": LogicalNameForm,
    "element-raw": RawElementView
  },
  props: ["smartElement"],
  watch: {
    smartElement(newValue) {
      this.$refs.splitter.disableEmptyContent();
      this.initFilters(window.location.search);
      this.selectedElement = newValue;
    }
  },
  computed: {
    urlConfig() {
      return `/api/v2/devel/security/elements/config/`;
    }
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
      pageableConfig: { pageSizes: [100, 200, 500], pageSize: 100 },
      viewURL: "",
      viewType: "html",
      viewRawContent: "",
      viewComponent: null,
      viewComponentProps: {}
    };
  },
  devCenterRefreshData() {
    if (this.$refs.grid) {
      this.$refs.grid.refreshGrid(true);
    }
  },
  created() {
    interceptDOMLinks("body", path => {
      const baseUrl = path.split("?")[0];
      const params = path.split("?")[1];
      if (baseUrl.includes("/devel/smartElements/")) {
        if (params) {
          this.initFilters(params);
          const re = /(name)=([^&]+)/g;
          let match;
          while ((match = re.exec(params))) {
            if (match && match.length >= 3) {
              const field = match[1];
              const value = decodeURIComponent(match[2]);
              if (field === "name") {
                this.$refs.splitter.disableEmptyContent();
                this.selectedElement = {
                  url: `${value}/view`,
                  component: "element-view",
                  props: {
                    initid: value,
                    viewId: "!defaultConsultation"
                  },
                  name: value,
                  label: value
                };
              }
            }
          }
        }
      }
    });
  },
  mounted() {
    if (this.selectedElement) {
      this.$refs.splitter.disableEmptyContent();
    }
    this.initFilters(window.location.search);
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(name|title|initid|fromid)=([^&]+)/g;
        let match;
        const filters = [];
        while ((match = re.exec(searchUrl))) {
          if (match && match.length >= 3) {
            const field = match[1];
            const value = decodeURIComponent(match[2]);
            filters.push({
              field,
              operator: field === "initid" ? "equals" : "contains",
              value
            });
          }
        }
        if (filters.length) {
          this.$refs.grid.dataSource.filter(filters);
        }
      };
      if (this.$refs.grid.kendoGrid) {
        computeFilters();
      } else {
        this.$refs.grid.$once("grid-ready", () => {
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
      let childrenFilters = {};
      if (this.$refs.component && this.$refs.component.getFilter) {
        childrenFilters = this.$refs.component.getFilter();
      }
      if (this.$refs.grid && this.$refs.grid.kendoGrid) {
        const currentFilter = this.$refs.grid.kendoGrid.dataSource.filter();
        if (currentFilter) {
          const filters = currentFilter.filters;
          return filters.reduce((acc, curr) => {
            acc[curr.field] = curr.value;
            return acc;
          }, childrenFilters);
        }
      }
      return childrenFilters;
    },
    getRoute() {
      const filter = this.getFilter();
      const filterUrl = Object.keys(filter).length ? `?${$.param(filter)}` : "";
      if (this.selectedElement) {
        return Promise.resolve([
          Object.assign({}, this.selectedElement, {
            url: this.selectedElement.url + filterUrl
          })
        ]);
      }
      return Promise.resolve([{ url: filterUrl }]);
    },
    actionClick(event) {
      let seIdentifier;
      switch (event.data.type) {
        case "consult":
          seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
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
          seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
          this.$refs.splitter.disableEmptyContent();
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/element?formatType=json`,
            component: "element-raw",
            props: {
              elementId: seIdentifier,
              elementType: docTypeString(event.data.row.properties.doctype),
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
          seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
          this.$refs.splitter.disableEmptyContent();
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/element?formatType=xml`,
            component: "element-raw",
            props: {
              elementId: seIdentifier,
              elementType: docTypeString(event.data.row.properties.doctype),
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
          seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
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
          if (event.data.row.properties.profid) {
            seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
            this.$refs.splitter.disableEmptyContent();
            event.preventDefault();
            this.selectedElement = {
              url: `${seIdentifier}/security?profileId=${event.data.row.properties.profid}`,
              component: "element-security",
              props: {
                profileId: event.data.row.properties.profid,
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
        case "logicalName":
          seIdentifier = event.data.row.properties.initid;
          this.$refs.splitter.disableEmptyContent();
          event.preventDefault();
          this.selectedElement = {
            url: `/api/v2/devel/smart-elements/logical-name/${seIdentifier}`,
            component: "element-logical-name",
            props: {
              properties: event.data.row.properties
            },
            name: seIdentifier,
            label: seIdentifier
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
      }
    },
    onRefresh() {
      this.$refs.grid.refreshGrid(true);
    }
  }
};
