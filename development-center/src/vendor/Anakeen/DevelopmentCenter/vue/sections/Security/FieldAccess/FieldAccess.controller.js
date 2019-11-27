import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import RightsGrid from "devComponents/profile/profile.vue";
import FallConfig from "devComponents/FieldAccessConfig/FieldAccessConfig.vue";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "fall-rights": RightsGrid,
    "fall-config": FallConfig
  },
  props: ["fieldAccess"],
  watch: {
    fieldAccess(newValue) {
      this.$refs.fallSplitter.disableEmptyContent();
      this.initFilters(window.location.search);
      this.selectedFieldAccess = newValue;
    }
  },
  data() {
    return {
      selectedFieldAccess: this.fieldAccess,
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
    if (this.$refs.fallGrid && this.$refs.fallGrid.dataSource) {
      this.$refs.fallGrid.dataSource.read();
    }
  },
  mounted() {
    if (this.selectedFieldAccess) {
      this.$refs.fallSplitter.disableEmptyContent();
    }
    this.initFilters(window.location.search);
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(name|title|fall_famid|dpdoc_famid)=([^&]+)/g;
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
          this.$refs.fallGrid.dataSource.filter(filters);
        }
      };
      if (this.$refs.fallGrid.kendoGrid) {
        computeFilters();
      } else {
        this.$refs.fallGrid.$once("grid-ready", () => {
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
      if (this.$refs.fallGrid && this.$refs.fallGrid.kendoGrid) {
        const currentFilter = this.$refs.fallGrid.kendoGrid.dataSource.filter();
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
          case "dpdoc_famid":
          case "fall_famid":
            if (event.data.cellData.name) {
              event.data.cellRender.html(
                `<a data-role="develRouterLink" href="/devel/smartStructures/${event.data.cellData.name}/infos">${event.data.cellData.name}</a>`
              );
            } else {
              event.data.cellRender.text("");
            }
            break;
          case "title":
            event.data.cellRender.html(
              `<a data-role="develRouterLink" href="/devel/smartElements/${event.data.rowData.id}/view?initid=${
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
      if (this.selectedFieldAccess) {
        return Promise.resolve([
          Object.assign({}, this.selectedFieldAccess, {
            url: this.selectedFieldAccess.url + filterUrl
          })
        ]);
      }
      return Promise.resolve([{ url: filterUrl }]);
    },
    actionClick(event) {
      let fallIdentifier;
      switch (event.data.type) {
        case "rights":
          this.$refs.fallSplitter.disableEmptyContent();
          fallIdentifier = event.data.row.name || event.data.row.initid;
          this.selectedFieldAccess = {
            url: `${fallIdentifier}/rights`,
            component: "fall-rights",
            props: {
              onlyExtendedAcls: true,
              profileId: fallIdentifier
            },
            name: fallIdentifier,
            label: fallIdentifier
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
        case "config":
          this.$refs.fallSplitter.disableEmptyContent();
          fallIdentifier = event.data.row.name || event.data.row.initid;
          this.selectedFieldAccess = {
            url: `${fallIdentifier}/config`,
            component: "fall-config",
            props: {
              fallid: fallIdentifier
            },
            name: fallIdentifier,
            label: fallIdentifier
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
          });
          break;
      }
      this.$refs.fallSplitter.disableEmptyContent();
    }
  }
};
