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
  props: ["wflIdentifier"],
  data() {
    return {
      transitionsDataSource: "",
      columnsTabMultiple: [
        "mailtemplates",
        "volatileTimers",
        "unAttachTimers",
        "persistentTimers"
      ]
    };
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.transitionsGridContent) {
        this.$refs.transitionsGridContent.kendoWidget().resize();
      }
    });
  },
  methods: {
    getTransitions(options) {
      this.$http
        .get(`/api/v2/devel/smart/workflows/${this.wflIdentifier}.json`, {
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
    parseTransitionsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data;
      }
      return [];
    },
    refreshTransitions() {
      this.$refs.transitionsGridContent.kendoWidget().dataSource.filter({});
      this.$refs.transitionsGridContent.kendoWidget().dataSource.read();
    },
    displayMultiple(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        if (dataItem[colId] instanceof Object) {
          if (this.columnsTabMultiple.includes(colId)) {
            if (dataItem[colId].length > 1) {
              let str = "";
              return this.recursiveData(dataItem[colId], str);
            } else {
              return dataItem[colId][0] ? dataItem[colId][0] : "";
            }
          }
        }
        return dataItem[colId];
      };
    },
    recursiveData(items, str) {
      if (items instanceof Object) {
        Object.keys(items.toJSON()).forEach(item => {
          if (items[item] instanceof Object) {
            this.recursiveData(items[item], str);
          } else {
            str += "<li>" + items[item] + "</li>";
          }
        });
      }
      return str;
    }
  }
};
