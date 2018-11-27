import Vue from "vue";
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
      transitionsDataSource: "",
      routeTab: [
        "Wfl::transitions::mail",
        "Wfl::transitions::timers::volatile",
        "Wfl::transitions::timers::unattach",
        "Wfl::transitions::timers::persistent"
      ],
      columnsTabMultiple: [
        "mailtemplates",
        "volatileTimers",
        "unAttachTimers",
        "persistentTimers"
      ],
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        }
      ]
    };
  },
  devCenterRefreshData() {
    this.refreshTransitions();
  },
  methods: {
    getTransitions(options) {
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
    parseTransitionsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.transitions;
      }
      return [];
    },
    refreshTransitions() {
      this.$refs.transitionsGridContent.kendoWidget().dataSource.read();
    },
    displayMultiple(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        if (dataItem[colId] instanceof Object) {
          if (this.columnsTabMultiple.includes(colId)) {
            let str = "";
            return this.recursiveData(dataItem[colId], str, colId);
          }
        }
        return dataItem[colId];
      };
    },
    recursiveData(items, str, colId) {
      if (items instanceof Object) {
        Object.keys(items.toJSON()).forEach(item => {
          if (items[item] instanceof Object) {
            this.recursiveData(items[item], str, colId);
          } else {
            switch (colId) {
              case "mailtemplates":
                str += `<a data-role="develRouterLink" href="/devel/smartElements/${
                  items[item]
                }/view">${items[item]}</a>&nbsp`;
                break;
              case "volatileTimers":
                str += `<a data-role="develRouterLink" href="//devel/smartElements/${
                  items[item]
                }/view">${items[item]}</a>&nbsp`;
                break;
              case "persistentTimers":
                str += `<a data-role="develRouterLink" href="/devel/smartElements/${
                  items[item]
                }/view">${items[item]}</a>&nbsp`;
                break;
              case "unAttachTimers":
                str += `<a data-role="develRouterLink" href="/devel/smartElements/${
                  items[item]
                }/view">${items[item]}</a>&nbsp`;
                break;
              default:
                break;
            }
          }
        });
      }
      return str;
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    }
  }
};
