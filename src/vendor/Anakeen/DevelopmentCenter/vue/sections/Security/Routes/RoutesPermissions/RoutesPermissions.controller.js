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
  },
  beforeRouteEnter(to, from, next) {
    if (to.query.accesName) {
      let filter = to.query.accesName;
      next(function(vueInstance) {
        if (filter && filter !== "") {
          vueInstance.$refs.routesPermissionsContent
            .kendoWidget()
            .dataSource.filter({
              field: "accessName",
              operator: "contains",
              value: filter
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
            return `<a data-role="develRouterLink" href="/devel/security/routes/access/controls/?accesName=${
              dataItem[colId]
            }" style="text-decoration: underline; color: #157EFB">${
              dataItem[colId]
            }</a>`;
          case "account":
            if (dataItem[colId].type === "role") {
              return `<a data-role="develRouterLink" href="/devel/security/roles/?role=${
                dataItem[colId].reference
              }" style="text-decoration: underline; color: #157EFB">${
                dataItem[colId].reference
              }</a>`;
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