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
      transitionsDataSource: ""
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
    displayMultiple(item, colId) {
      if (item instanceof Object) {
        let str = "";
        return this.recursiveData(item, str, colId);
      }
      return item;
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
                }/view/?filters=${this.$.param({ name: items[item] })}">${
                  items[item]
                }</a>`;
                break;
              case "volatileTimers":
                str += `<a data-role="develRouterLink" href="/devel/smartElements/${
                  items[item]
                }/view/?filters=${this.$.param({
                  name: items[item]
                })}">${items[item]}</a>`;
                break;
              case "persistentTimers":
                str += `<a data-role="develRouterLink" href="/devel/smartElements/${
                  items[item]
                }/view/?filters=${this.$.param({
                  name: items[item]
                })}">${items[item]}</a>`;
                break;
              case "unAttachTimers":
                str += `<a data-role="develRouterLink" href="/devel/smartElements/${
                  items[item]
                }/view/?filters=${this.$.param({
                  name: items[item]
                })}">${items[item]}</a>`;
                break;
              default:
                break;
            }
          }
        });
      }
      return str;
    },
    displayData(colId) {
      return dataItem => {
        let str = "";
        switch (colId) {
          case "transition":
            str = `<div class="transitions-infos"><ul><li><b>Name :&nbsp</b>${this.checkIsValid(
              dataItem["id"]
            )}</li><li><b>Label :&nbsp</b>${this.checkIsValid(
              dataItem["label"]
            )}</li></ul></div>`;
            return str;
        }
      };
    },
    checkIsValid(item) {
      if (item === null || item === undefined) {
        return "";
      } else {
        return item;
      }
    },
    displayLink(colId) {
      return dataItem => {
        switch (colId) {
          case "timers":
            return `<div><b>Volatile timers&nbsp:&nbsp</b>${this.displayMultiple(
              this.checkIsValid(dataItem["volatileTimers"]),
              "volatileTimers"
            )}</div><div><b>Persistent timers&nbsp:&nbsp</b>${this.displayMultiple(
              this.checkIsValid(dataItem["persistentTimers"]),
              "persistentTimers"
            )}</div><div><b>Unattach timers&nbsp:&nbsp</b>${this.displayMultiple(
              this.checkIsValid(dataItem["unAttachTimers"]),
              "unAttachTimers"
            )}</div>`;
          case "mailtemplates":
            return `${this.displayMultiple(
              this.checkIsValid(dataItem["mailtemplates"]),
              "mailtemplates"
            )}`;
        }
      };
    }
  }
};
