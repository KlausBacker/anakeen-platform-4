import Vue from "vue";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
Vue.use(ButtonsInstaller);

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
      this.$refs.routesPermissionsContent.kendoWidget().resize();
    });
  },
  beforeRouteEnter(to, from, next) {
    if (to.query.filter) {
      let filter = to.query.filter.logicalName;
      next(function(vueInstance) {
        //vueInstance.$refs.routesPermissionsContent.$on("grid-ready", () => {
        if (filter && filter !== "") {
          filter = filter.split(" and ").map(String);
          vueInstance.$refs.routesPermissionsContent
            .kendoWidget()
            .dataSource.filter({
              logic: "or",
              filters: filter.map(andFilters => {
                const filtersToAdd = andFilters.split("::")[1];
                return {
                  field: "accessName",
                  operators: "contains",
                  value: filtersToAdd
                };
              })
            });
        }
      });
    } else {
      next(function(vueInstance) {
        vueInstance.$refs.routesPermissionsContent
          .kendoWidget()
          .dataSource.filter({});
      });
    }
  },
  methods: {
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
      return [];
    },
    parsePermissionsTotal(response) {
      return response.data.data.requestParameters.total;
    },
    refreshPermissions() {
      this.$refs.routesPermissionsContent.kendoWidget().dataSource.filter({});
      this.$refs.routesPermissionsContent.kendoWidget().dataSource.read();
    },
    disabledFilter(args) {
      args.element.kendoDropDownList({
        valuePrimitive: true,
        dataSource: ["true", "false"]
      });
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    }
  }
};
