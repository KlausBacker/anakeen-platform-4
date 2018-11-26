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
      stepsDataSource: "",
      ssName: "",
      routeTab: ["Wfl::steps::pdoc"],
      columnsTabMultiple: ["mailtemplates"],
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
        this.ssName = response.data.data.properties.structure;
        this.$refs.stepsGridContent.kendoWidget().autoFitColumn(0);
        this.$refs.stepsGridContent.kendoWidget().autoFitColumn(1);
        return response.data.data.steps;
      }
      return [];
    },
    refreshSteps() {
      this.$refs.stepsGridContent.kendoWidget().dataSource.filter({});
      this.$refs.stepsGridContent.kendoWidget().dataSource.read();
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayColor(colId) {
      return dataItem => {
        return `<div class='chip-color' style='background-color:${
          dataItem[colId]
        }'></div><span>&nbsp${dataItem[colId]}</span>`;
      };
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
                str += `<li><a data-role="develRouterLink" href="/devel/smartElements/${
                  items[item]
                }/view">${items[item]}</a></li>`;
                break;
              default:
                break;
            }
          }
        });
      }
      return str;
    },
    displayLink(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        } else {
          switch (colId) {
            case "viewcontrol":
              return `<a data-role="develRouterLink" href="/devel/ui/${
                this.ssName
              }/views">${dataItem[colId]}</a>&nbsp`;

            case "mask":
              return `<a data-role="develRouterLink" href="/devel/ui/${
                this.ssName
              }/views">${dataItem[colId]}</a>&nbsp`;
            case "profil":
              return `<a data-role="develRouterLink" href="/devel/security/profiles/${
                dataItem[colId]
              }">${dataItem[colId]}</a>&nbsp`;
            case "fall":
              return `<a data-role="develRouterLink" href="/devel/security/workflows/${
                dataItem[colId]
              }/accesses">${dataItem[colId]}</a>&nbsp`;
            case "timer":
              return `<a data-role="develRouterLink" href="/devel/smartElements/${
                dataItem[colId]
              }/view">${dataItem[colId]}</a>&nbsp`;
            default:
              return dataItem[colId];
          }
        }
      };
    }
  }
};
