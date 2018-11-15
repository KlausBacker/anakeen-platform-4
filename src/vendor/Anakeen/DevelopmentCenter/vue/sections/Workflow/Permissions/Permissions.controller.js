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
      if (this.$refs.permissionsGridContent) {
        this.$refs.permissionsGridContent.kendoWidget().resize();
      }
    });
  },
  methods: {
    getPermissions(options) {
      return [];
    },
    parsePermissionsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data;
      }
      return [];
    },
    refreshPermissions() {
      this.$refs.permissionsGridContent.kendoWidget().dataSource.filter({});
      this.$refs.permissionsGridContent.kendoWidget().dataSource.read();
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
