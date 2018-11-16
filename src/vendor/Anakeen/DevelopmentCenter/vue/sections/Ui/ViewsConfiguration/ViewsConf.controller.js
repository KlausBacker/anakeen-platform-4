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
  props: ["ssName"],
  data() {
    return {
      viewsDataSource: ""
    };
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.viewsGridContent) {
        this.$refs.viewsGridContent.kendoWidget().resize();
      }
    });
  },
  methods: {
    getViews(options) {
      this.$http
        .get(`/api/v2/devel/ui/smart/structures/${this.ssName}/views/`, {
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
    parseViewsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.views;
      }
      return [];
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    }
  }
};
