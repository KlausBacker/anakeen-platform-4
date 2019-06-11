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
      tabMultiple: []
    };
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.routesGridContent) {
        this.$refs.routesGridContent.kendoWidget().resize();
      }
    });
    this.initFilters(window.location.search);
  },
  devCenterRefreshData() {
    if (this.$refs.routesGrid) {
      this.$refs.routesGrid.kendoWidget().read();
    }
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(nameSpace|name|method|requiredAccess|decscription)=([^&]+)/g;
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
          this.$refs.routesGrid.kendoWidget().filter(filters);
        }
      };
      if (this.$refs.routesGrid) {
        computeFilters();
      } else {
        this.$refs.routesGrid.$once("hook:mounted", () => {
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
      if (this.$refs.routesGrid && this.$refs.routesGrid.kendoWidget()) {
        const currentFilter = this.$refs.routesGrid.kendoWidget().filter();
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
    getRoutes(options) {
      this.$http
        .get("/api/v2/devel/security/routes/", {
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
    parseRoutesData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.routes;
      }
      return [];
    },
    parseRoutesTotal(response) {
      return response.data.data.requestParameters.total;
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayMultiple(e) {
      this.tabMultiple = [];
      if (e && e.requiredAccess) {
        Object.keys(e.requiredAccess.toJSON()).forEach(key => {
          const elt = e.requiredAccess.toJSON()[key];
          elt.map(e => {
            let accessName = e.split("::")[1];
            this.tabMultiple.push(
              `<a data-role="develRouterLink" href="/devel/security/routes/access/permissions/?accessName=${accessName}">${accessName}</a>`
            );
          });
        });
      }
      return this.tabMultiple
        .toString()
        .replace(new RegExp(",", "g"), " <b>and</b> ");
    }
  }
};
