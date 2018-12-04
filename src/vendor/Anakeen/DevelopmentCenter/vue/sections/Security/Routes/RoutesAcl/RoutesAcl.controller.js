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
  },
  beforeRouteEnter(to, from, next) {
    if (to.query.filters) {
      let filter = to.query.filters.split("=");
      next(function(vueInstance) {
        if (filter && filter !== "") {
          vueInstance.$refs.routesGridContent.kendoWidget().dataSource.filter({
            field: filter[0],
            operator: "contains",
            value: filter[1]
          });
        }
      });
    } else {
      next(function(vueInstance) {
        vueInstance.$refs.routesGridContent.kendoWidget().dataSource.filter({});
      });
    }
  },
  devCenterRefreshData() {
    if (this.$refs.routesGrid) {
      this.$refs.routesGrid.kendoWidget().read();
    }
  },
  methods: {
    bindFilters() {
      this.$refs.routesGridContent.kendoWidget().bind("filter", e => {
        console.log(e);
        if (e.filter === null) {
          let query = Object.assign({}, this.$route.query);
          delete query.filters;
          this.$router.replace({ query });
        }
      });
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
              `<a data-role="develRouterLink" href="/devel/security/routes/access/permissions/??filters=${this.$.param(
                { accessName: accessName }
              )}">${accessName}</a>`
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
