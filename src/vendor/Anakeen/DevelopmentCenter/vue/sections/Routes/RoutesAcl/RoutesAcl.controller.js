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
      tabMultiple: []
    };
  },
  beforeRouteEnter(to, from, next) {
    if (to.query.filter) {
      let filter = to.query.filter;
      next(function(vueInstance) {
        if (filter && filter !== "") {
          vueInstance.$refs.routesGridContent.kendoWidget().dataSource.filter({
            field: "requiredAccess",
            operator: "contains",
            value: filter
          });
        }
      });
    } else {
      next(function(vueInstance) {
        vueInstance.$refs.routesGridContent.kendoWidget().dataSource.filter({});
      });
    }
  },
  methods: {
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
        return response.data.data;
      }
      return [];
    },
    refreshRoutes() {
      this.$refs.routesGridContent.kendoWidget().dataSource.filter({});
      this.$refs.routesGridContent.kendoWidget().dataSource.read();
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
              `<a href="/devel/security/routes/access/permissions/?filter=${accessName}" style="text-decoration: underline; color: #157EFB">${accessName}</a>`
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
