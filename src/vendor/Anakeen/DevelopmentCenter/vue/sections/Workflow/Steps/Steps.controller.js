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
  props: ["wflName"],
  data() {
    return {
      stepsDataSource: "",
      ssName: ""
    };
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.stepsGridContent) {
        this.$refs.stepsGridContent.kendoWidget().resize();
      }
    });
  },
  methods: {
    getSteps(options) {
      this.$http
        .get(`/api/v2/devel/smart/workflows/${this.wflName}`, {
          params: options.data,
          paramsSerializer: kendo.jQuery.param
        })
        .then(response => {
          options.success(response);
        })
        .catch(response => {
          options.error(response);
        });
      return [];
    },
    parseStepsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.steps;
      }
      return [];
    },
    refreshSteps() {
      this.$refs.stepsGridContent.kendoWidget().dataSource.filter({});
      this.$refs.stepsGridContent.kendoWidget().dataSource.read();
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
