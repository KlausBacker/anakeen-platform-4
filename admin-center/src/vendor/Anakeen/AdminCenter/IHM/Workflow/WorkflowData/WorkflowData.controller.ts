import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.color.js";
import "@progress/kendo-ui/js/kendo.colorpicker.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import { Component, Prop, Vue, Watch, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
@Component
export default class WorkflowDataController extends Mixins(AnkI18NMixin) {
  public gridWidget: kendo.ui.Grid = null;
  public language: string = "";

  public $refs!: {
    wflGridContent: Grid;
  };

  @Prop({ type: String, default: "" })
  public wflName;
  @Watch("wflName")
  public watchWflName() {
    this.gridWidget.dataSource.read();
  }
  public mounted() {
    this.gridWidget = this.$refs.wflGridContent.kendoWidget() as kendo.ui.Grid;
    this.$http
      .get(`/api/v2/ui/users/current`)
      .then(response => (this.language = response.data.locale === "fr_FR.UTF-8" ? "fr" : "en"));
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
          .put(`/api/v2/admin/workflow/data/${this.wflName}/${rowData.id}`, { color: e.value }, jsonHeader)
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
    const result = [];
    if (response && response.data && response.data.data) {
      response.data.data.steps.forEach(item => {
        item.type = this.$t("AdminCenterWorkflow.steps");
        result.push(item);
      });
      response.data.data.transitions.forEach(item => {
        item.type = this.$t("AdminCenterWorkflow.transitions");
        result.push(item);
      });
      return result;
    }
    return [];
  }
  public displayData(colId) {
    return dataItem => {
      switch (colId) {
        case "type":
          return `<ul><li><b>${this.$t("AdminCenterWorkflow.Id")}:&nbsp</b>${dataItem.id}</li><li><b>${this.$t(
            "AdminCenterWorkflow.Type"
          )}:&nbsp</b>${dataItem.type}</li></ul>`;
        case "info":
          if (dataItem.type === this.$t("AdminCenterWorkflow.steps")) {
            return `<ul><li><b>${this.$t(
              "AdminCenterWorkflow.Label"
            )}:&nbsp</b><a data-role="adminRouterLink" href="/admin/i18n/${this.language}?section=Workflow&msgstr=${
              dataItem.label
            }&msgid=${dataItem.id}">${dataItem.label}</a></li><li><b>${this.$t(
              "AdminCenterWorkflow.Timer"
            )}:&nbsp</b>${this.displayMultiple(dataItem.timer, "timer")}</li><li><b>${this.$t(
              "AdminCenterWorkflow.Mail template"
            )}:&nbsp</b>${this.displayMultiple(dataItem.mailtemplates, "mail")}</li><li><b>${this.$t(
              "AdminCenterWorkflow.Color"
            )}:&nbsp</b><input data-role="colorpicker" type="color" class="wfl-step__color" value="${
              dataItem.color
            }"/></li>`;
          } else {
            return `<ul><li><b>${this.$t(
              "AdminCenterWorkflow.Label"
            )}:&nbsp</b><a data-role="adminRouterLink" href="/admin/i18n/${this.language}?section=Workflow&msgstr=${
              dataItem.label
            }&msgid=${dataItem.id}">${dataItem.label}</a></li><li><b>${this.$t(
              "AdminCenterWorkflow.Persistent timer"
            )}:&nbsp</b>${this.displayMultiple(dataItem.persistentTimers, "timer")}</li><li><b>${this.$t(
              "AdminCenterWorkflow.Unattach timer"
            )}:&nbsp</b>${this.displayMultiple(dataItem.unAttachTimers, "timer")}</li><li><b>${this.$t(
              "AdminCenterWorkflow.Volatile timer"
            )}:&nbsp</b>${this.displayMultiple(dataItem.volatileTimers, "timer")}</li><li><b>${this.$t(
              "AdminCenterWorkflow.Mail template"
            )}:&nbsp</b>${this.displayMultiple(dataItem.mailtemplates, "mail")}</li>`;
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
      return `${this.$t("AdminCenterWorkflow.None")}`.fontcolor("ced4da");
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
                str += `<li><a data-role="adminRouterLink" href="/admin/mail/?name=${items[item]}">${
                  items[item]
                }</a></li>`;
                break;
              case "timer":
                str += `<li><a data-role="adminRouterLink" href="/admin/timer/?name=${items[item]}">${
                  items[item]
                }</a></li>`;
                break;
              default:
                break;
            }
          }
        }
      });
    }
    if (str === "") {
      return `${this.$t("AdminCenterWorkflow.None")}`.fontcolor("ced4da");
    }
    return `<ul>${str}</ul>`;
  }
  public autoFilterCol(e) {
    e.element.addClass("k-textbox filter-input");
  }
}
