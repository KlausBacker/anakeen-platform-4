import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";

declare var kendo;

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
@Component
export default class WorkflowDataController extends Vue {
  public tabData = [];
  @Prop({ type: String, default: "" }) public wflName;

  public getWfl(options) {
    this.$http
      .get(`/api/v2/admin/workflow/data/${this.wflName}`, {
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
  }

  public parseWflData(response) {
    if (response && response.data && response.data.data) {
      response.data.data.steps.forEach(item => {
        item.type = "steps";
        this.tabData.push(item);
      });
      response.data.data.transitions.forEach(item => {
        item.type = "transitions";
        this.tabData.push(item);
      });
      console.log("lol", this.tabData);
      return this.tabData;
    }
    return [];
  }
  public displayData(colId) {
    return dataItem => {
      switch (colId) {
        case "type":
          return `<ul><li><b>Id:&nbsp</b>${dataItem.id}</li><li><b>Type:&nbsp</b>${dataItem.type}</li></ul>`;
          break;
        case "info":
          if (dataItem.type === "steps") {
            return `<ul><li><b>Label:&nbsp</b>${dataItem.label}</li><li><b>Timer:&nbsp</b>${dataItem.timer}</li><li><b>Mail template:&nbsp</b>${dataItem.mailtemplates}</li><li><b>Color:&nbsp</b></li>`;
          } else {
            return `<ul><li><b>Label:&nbsp</b>${dataItem.label}</li><li><b>Timer:&nbsp</b>${dataItem.volatileTimers}</li><li><b>Mail template:&nbsp</b>${dataItem.mailtemplates}</li>`;
          }
          break;
        default:
          break;
      }
    };
  }
}
