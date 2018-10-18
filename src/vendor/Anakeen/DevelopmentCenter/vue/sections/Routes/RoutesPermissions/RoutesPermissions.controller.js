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
      if (this.$refs.routesPermissionsContent) {
        this.$refs.routesPermissionsContent.kendoWidget().resize();
      }
    });
  },
  beforeRouteEnter(to, from, next) {
    if (to.query.filter) {
      let filter = to.query.filter;
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
      return [];
    },
    parsePermissionsTotal(response) {
      return response.data.data.requestParameters.total;
    },
    refreshPermissions() {
      this.$refs.routesPermissionsContent.kendoWidget().dataSource.filter({});
      this.$refs.routesPermissionsContent.kendoWidget().dataSource.read();
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayLink(e) {
      const accessName = e.accessName;
      return `<a href="/devel/security/routes/access/controls/?filter=${accessName}" style="text-decoration: underline; color: #157EFB">${accessName}</a>`;
    }
  }
};
