import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import ElementView from "../../SmartElements/ElementView/ElementView.vue";
import ProfileView from "devComponents/profile/profile.vue";
import Splitter from "@anakeen/internal-components/lib/Splitter.js";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "element-view": ElementView,
    "permissions-view": ProfileView
  },
  props: ["ssName", "controlConfig"],
  watch: {
    controlConfig(newValue) {
      this.$refs.controlSplitter.disableEmptyContent();
      this.selectedControl = newValue;
      this.initFilters(window.location.search);
      this.getSelected(newValue.name);
    }
  },
  mounted() {
    const searchUrl = window.location.search;
    if (this.selectedControl) {
      this.$refs.controlSplitter.disableEmptyContent();
    }
    this.initFilters(searchUrl);
  },
  data() {
    return {
      selectedControl: this.controlConfig,
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
    if (this.$refs.controlConfGrid && this.$refs.controlConfGrid.dataSource) {
      this.$refs.controlConfGrid.dataSource.read();
    }
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(name|title)=([^&]+)/g;
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
          this.$refs.controlConfGrid.dataSource.filter(filters);
        }
      };
      if (this.$refs.controlConfGrid.kendoGrid) {
        computeFilters();
      } else {
        this.$refs.controlConfGrid.$once("grid-ready", () => {
          computeFilters();
        });
      }
    },
    onGridDataBound() {
      this.getRoute().then(route => {
        this.$emit("navigate", route);
      });
    },
    getSelected(e) {
      this.$nextTick(() => {
        if (e !== "") {
          if (this.$refs.controlConfGrid.kendoGrid) {
            this.$("[role=row]", this.$el).removeClass(" control-view-is-opened");
            this.$(
              "[data-uid=" +
                this.$refs.controlConfGrid.kendoGrid.dataSource.view().find(d => d.rowData.name === e).uid +
                "]",
              this.$el
            ).addClass(" control-view-is-opened");
          }
        }
      });
    },
    getFilter() {
      if (this.$refs.controlConfGrid && this.$refs.controlConfGrid.kendoGrid) {
        const currentFilter = this.$refs.controlConfGrid.kendoGrid.dataSource.filter();
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
      if (this.selectedControl) {
        return Promise.resolve([
          Object.assign({}, this.selectedControl, {
            url: this.selectedControl.url + filterUrl
          })
        ]);
      }
      return Promise.resolve([{ url: filterUrl }]);
    },
    actionClick(event) {
      const controlName = event.data.row.name;
      switch (event.data.type) {
        case "consult":
          event.preventDefault();
          this.$refs.controlSplitter.disableEmptyContent();
          this.selectedControl = {
            url: `/element/${controlName}`,
            component: "element-view",
            props: {
              initid: controlName
            },
            name: controlName,
            label: controlName
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(event.data.row.name);
          });
          break;
        case "permissions":
          event.preventDefault();
          this.$refs.controlSplitter.disableEmptyContent();
          this.selectedControl = {
            url: `/permissions/${controlName}`,
            component: "permissions-view",
            props: {
              profileId: controlName.toString(),
              detachable: true,
              onlyExtendedAcls: true
            },
            name: controlName,
            label: controlName
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(event.data.row.name);
          });
          break;
        default:
          break;
      }
    }
  }
};
