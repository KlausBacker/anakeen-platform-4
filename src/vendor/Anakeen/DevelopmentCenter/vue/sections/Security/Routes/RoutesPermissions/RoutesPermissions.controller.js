import Vue from "vue";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);

export default {
  components: {
    Grid
  },
  data() {
    return {
      permissionsDataSource: ""
    };
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.routesPermissionsContent) {
        this.$refs.routesPermissionsContent.kendoWidget().resize();
      }
    });
    this.initFilters(window.location.search);
  },
  devCenterRefreshData() {
    if (this.$refs.routesPermissions) {
      this.$refs.routesPermissions.kendoWidget().read();
    }
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(accessNs|accessName|account\.reference)=([^&]+)/g;
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
          this.$refs.routesPermissions.kendoWidget().filter(filters);
        }
      };
      if (this.$refs.routesPermissions) {
        computeFilters();
      } else {
        this.$refs.routesPermissions.$once("hook:mounted", () => {
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
      if (
        this.$refs.routesPermissions &&
        this.$refs.routesPermissions.kendoWidget()
      ) {
        const currentFilter = this.$refs.routesPermissions
          .kendoWidget()
          .filter();
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
      return Promise.resolve([{ url: filterUrl }]);
    },
    getPermissions(options) {
      this.$http
        .get("/api/v2/devel/security/routes/accesses/", {
          params: options.data,
          paramsSerializer: kendo.jQuery.param
        })
        .then(response => {
          options.success(response);
        })
        .catch(response => {
          options.error(response);
        });
    },
    parsePermissionsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.access;
      }
    },
    parsePermissionsTotal(response) {
      return response.data.data.requestParameters.total;
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayLink(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        switch (colId) {
          case "accessName":
            return `<a data-role="develRouterLink" href="/devel/security/routes/access?${this.$.param(
              { requiredAccess: dataItem[colId] }
            )}">${dataItem[colId]}</a>`;
          case "account":
            if (dataItem[colId].type === "role") {
              return `<a data-role="develRouterLink" href="/devel/security/roles/?role_login=${
                dataItem[colId].reference
              }">${dataItem[colId].reference}</a>`;
            } else {
              return dataItem[colId].reference;
            }
          default:
            break;
        }
      };
    }
  }
};
