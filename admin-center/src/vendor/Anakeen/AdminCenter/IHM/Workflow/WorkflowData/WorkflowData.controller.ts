import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.color.js";
import "@progress/kendo-ui/js/kendo.colorpicker.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";

declare var kendo;

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
@Component
export default class WorkflowDataController extends Vue {
  public tabData = [];
  public language: string = "";
  @Prop({ type: String, default: "" }) public wflName;
  @Watch("wflName")
  public watchWflName() {
    const grid = $(".wfl-grid-content");
    grid.data("kendoGrid").dataSource.read();
  }
  public mounted() {
    this.$http
      .get(`/api/v2/ui/users/current`)
      .then(
        response =>
          (this.language = response.data.locale === "fr_FR.UTF-8" ? "fr" : "en")
      );
  }
  public gridDataBound() {
    $(".wfl-step__color").kendoColorPicker({
      buttons: true,
      change: e => {
        const rowData: any = $(".wfl-grid-content")
          .data("kendoGrid")
          .dataItem($(e.sender.element).closest("tr[role=row]"));
        const jsonHeader = {
          headers: {
            "Content-type": "application/json"
          }
        };
        this.$http
          .put(
            `/api/v2/admin/workflow/data/${this.wflName}/${rowData.id}`,
            { color: e.value },
            jsonHeader
          )
          .then(response => {
            if (response.status === 200) {
              this.$emit("EditStepColorSuccess");
            } else {
              this.$emit("EditStepColorFail");
            }
          });
      }
    });
  }
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
    this.tabData = [];
    if (response && response.data && response.data.data) {
      response.data.data.steps.forEach(item => {
        item.type = "steps";
        this.tabData.push(item);
      });
      response.data.data.transitions.forEach(item => {
        item.type = "transitions";
        this.tabData.push(item);
      });
      return this.tabData;
    }
    return [];
  }
  public displayData(colId) {
    return dataItem => {
      switch (colId) {
        case "type":
          return `<ul><li><b>Id:&nbsp</b>${dataItem.id}</li><li><b>Type:&nbsp</b>${dataItem.type}</li></ul>`;
        case "info":
          if (dataItem.type === "steps") {
            return `<ul><li><b>Label:&nbsp</b><a href="/admin/i18n/${
              this.language
            }?msgstr=${dataItem.label}&msgid=${dataItem.id}">${
              dataItem.label
            }</a></li><li><b>Timer:&nbsp</b>${this.displayMultiple(
              dataItem.timer,
              "timer"
            )}</li><li><b>Mail template:&nbsp</b>${this.displayMultiple(
              dataItem.mailtemplates,
              "mail"
            )}</li><li><b>Color:&nbsp</b><input data-role="colorpicker" type="color" class="wfl-step__color" value="${
              dataItem.color
            }"/></li>`;
          } else {
            return `<ul><li><b>Label:&nbsp</b>${
              dataItem.label
            }</li><li><b>Persistent timer:&nbsp</b>${this.displayMultiple(
              dataItem.persistentTimers,
              "timer"
            )}</li><li><b>Unattach timer:&nbsp</b>${this.displayMultiple(
              dataItem.unAttachTimers,
              "timer"
            )}</li><li><b>Volatile timer:&nbsp</b>${this.displayMultiple(
              dataItem.volatileTimers,
              "timer"
            )}</li><li><b>Mail template:&nbsp</b>${this.displayMultiple(
              dataItem.mailtemplates,
              "mail"
            )}</li>`;
          }
        default:
          break;
      }
    };
  }
  public displayMultiple(data, type) {
    if (data instanceof Object) {
      const str = "";
      return this.recursiveData(data, str, type);
    } else if (data !== null && data !== undefined) {
      return data;
    } else {
      return "";
    }
  }
  public recursiveData(items, str, type) {
    if (items instanceof Object) {
      Object.keys(items.toJSON()).forEach(item => {
        if (items[item] instanceof Object) {
          this.recursiveData(items[item], str, type);
        } else {
          if (items[item]) {
            switch (type) {
              case "mail":
                str += `<li><a href="/admin/mail/?name=${items[item]}">${items[item]}</a></li>`;
                break;
              case "timer":
                str += `<li><a href="/admin/timer/?name=${items[item]}">${items[item]}</a></li>`;
                break;
              default:
                break;
            }
          }
        }
      });
    }
    return `<ul>${str}</ul>`;
  }
  public autoFilterCol(e) {
    e.element.addClass("k-textbox filter-input");
  }
}
