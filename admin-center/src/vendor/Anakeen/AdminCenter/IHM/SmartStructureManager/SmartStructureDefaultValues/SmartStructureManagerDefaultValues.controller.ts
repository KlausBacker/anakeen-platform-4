import ankSmartController from "@anakeen/user-interfaces/components/lib/AnkController";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import Vue from "vue";
import VModal from "vue-js-modal";
import { Component, Prop, Watch } from "vue-property-decorator";

Vue.use(VModal);
Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
@Component({
  components: {
    "smart-form": AnkSmartForm
  }
})
export default class SmartStructureManagerDefaultValuesController extends Vue {
  public smartForm: object = {};
  public unsupportedType = ["frame", "tab", "array"];
  public $refs!: {
    [key: string]: any;
  };
  @Prop({
    default: "",
    type: String
  })
  public ssName;
  public editWindow = {
    title: "",
    width: "50%"
  };

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.$refs.defaultGridContent.kendoWidget().dataSource.read();
    }
  }
  public onEditClick(e) {
    const row = $(e.target).closest("tr")[0];
    // const sf = row.children[0].textContent;
    const value = row.children[2].textContent;
    const type = row.children[1].textContent;
    this.$modal.show("ssm-modal", {
      config: {
        menu: [
          {
            beforeContent: '<div class="fa fa-times" />',
            htmlLabel: "",
            iconUrl: "",
            id: "cancel",
            important: false,
            label: "Cancel",
            target: "_self",
            type: "itemMenu",
            url: "#action/ssmanager.cancel",
            visibility: "visible"
          },
          {
            beforeContent: '<div class="fa fa-save" />',
            htmlLabel: "",
            iconUrl: "",
            id: "submit",
            important: false,
            label: "Submit",
            target: "_self",
            type: "itemMenu",
            url: "#action/ssmanager.save",
            visibility: "visible"
          }
        ],
        structure: [
          {
            content: [
              {
                enumItems: [
                  {
                    key: "inherited",
                    label: "Inherited"
                  },
                  {
                    key: "value",
                    label: "Value"
                  },
                  {
                    key: "advanced_value",
                    label: "Advanced Value"
                  },
                  {
                    key: "no_value",
                    label: "No value"
                  }
                ],
                label: "Type",
                name: "ssm_type",
                type: "enum"
              },
              {
                label: "Inherited",
                name: "ssm_inherited_value",
                type: "text"
              },
              {
                label: "Value",
                name: "ssm_value",
                type: `${type}`
              },
              {
                label: "Advanced value",
                name: "ssm_advanced_value",
                type: "longtext"
              }
            ],
            label: "Default value",
            name: "ssm_default_value",
            type: "frame"
          }
        ],
        title: "Edit value form",
        values: {
          ssm_inherited_value: `${value}`,
          ssm_type: "inherited"
        }
      }
    });
  }
  public ssmFormReady() {
    this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
    this.$refs.ssmForm.hideSmartField("ssm_value");
  }
  public ssmFormChange() {
    const smartForm = this.$refs.ssmForm;
    switch (smartForm.getValue("ssm_type").value) {
      case "inherited":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.showSmartField("ssm_inherited_value");
        break;
      case "value":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.showSmartField("ssm_value");
        smartForm.hideSmartField("ssm_inherited_value");
        break;
      case "advanced_value":
        smartForm.showSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.hideSmartField("ssm_inherited_value");
        break;
      default:
        this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
        this.$refs.ssmForm.hideSmartField("ssm_value");
        this.$refs.ssmForm.hideSmartField("ssm_inherited_value");
        break;
    }
  }
  public formClickMenu(e, se, params) {
    switch (params.eventId) {
      case "ssmanager.cancel":
        this.$modal.hide("ssm-modal");
        break;
      case "ssmanager.save":
        break;
    }
  }
  public beforeEdit(data) {
    this.smartForm = data.params.config;
  }
  public displayData(colId) {
    return dataItem => {
      switch (colId) {
        case "type":
          if (dataItem[colId]) {
            return dataItem[colId];
          }
          break;
        case "value":
          return this.displayMultiple(dataItem[colId]);
      }
    };
  }
  public displayMultiple(data) {
    if (data instanceof Object) {
      const str = "";
      return this.recursiveData(data, str);
    } else if (data !== null && data !== undefined) {
      return data;
    } else {
      return "None".fontcolor("ced4da");
    }
  }
  public recursiveData(items, str) {
    if (items instanceof Object) {
      Object.keys(items.toJSON()).forEach(item => {
        if (items[item] instanceof Object) {
          this.recursiveData(items[item], str);
        } else {
          if (items[item]) {
            str += `<li>${items[item]}</li>`;
          }
        }
      });
    }
    if (str === "") {
      return "";
    }
    return `<ul>${str}</ul>`;
  }
  protected parseDefaultValuesData(response) {
    const result = [];
    if (response && response.data && response.data.data) {
      const items = response.data.data.defaultValues;
      const fields = response.data.data.fields;
      Object.keys(items).map(item => {
        if (!this.unsupportedType.includes(items[item].type)) {
          let field =  fields.find(element => element.id === item)
          if(field)
          {
            // TODO : Refactor as a recursive function
            // Construct label /w parent architecture
            let constructingLabel = [field.labeltext]
            let finalLabel = "";
            while(field.parentId)
            {
              const parentField = fields.find(element => element.id === field.parentId)
              constructingLabel.push(parentField.labeltext)
              field = parentField;
            }
            constructingLabel = constructingLabel.reverse();
            finalLabel = constructingLabel.join(" / ");

              result.push({
                config: items[item].config,
                label: finalLabel,
                type: items[item].type,
                value: items[item].value,
              });
          }
        }
      });
      return result;
    }
    return [];
  }

  protected getDefaultValues(options) {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/defaults/`, {
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
  protected autoFilterCol(e) {
    e.element.addClass("k-textbox filter-input");
  }
}
