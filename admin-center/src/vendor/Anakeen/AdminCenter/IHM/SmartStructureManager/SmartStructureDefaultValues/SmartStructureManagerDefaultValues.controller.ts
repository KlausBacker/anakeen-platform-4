import ankSmartController from "@anakeen/user-interfaces/components/lib/AnkController";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import Vue from "vue";
import VModal from "vue-js-modal";
import { Component, Prop, Watch } from "vue-property-decorator";
import { privateEncrypt } from 'crypto';

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
  public finalData: Object = {
    type: "",
    value: "",
  }
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
    const rawValue = row.children[3].innerText;
    const parentValue = row.children[2].textContent;
    const type = JSON.parse(row.children[1].textContent);
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
                type: `${type.type}`,
                typeFormat: `${type.typeFormat}`,
                enumItems: '',
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
          ssm_inherited_value: `${parentValue}`,
          ssm_value: `${rawValue}`,
          ssm_advanced_value: "",
          ssm_type: "value"
        }
      }
    });
  }
  public ssmFormReady() {
    this.$refs.ssmForm.hideSmartField("ssm_inherited_value");
    this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
  }
  public ssmFormChange(e, smartStructure, smartField, values, index) {
    const smartForm = this.$refs.ssmForm;
    switch (smartForm.getValue("ssm_type").value) {
      case "inherited":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.showSmartField("ssm_inherited_value");
        this.finalData["value"] = smartForm.getValue("ssm_inherited_value").value;
        break;
      case "value":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.showSmartField("ssm_value");
        smartForm.hideSmartField("ssm_inherited_value");
        this.finalData["value"] = smartForm.getValue("ssm_value").value;
        break;
      case "advanced_value":
        smartForm.showSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.hideSmartField("ssm_inherited_value");
        this.finalData["value"] = smartForm.getValue("ssm_advanced_value").value;
        break;
      default:
        this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
        this.$refs.ssmForm.hideSmartField("ssm_value");
        this.$refs.ssmForm.hideSmartField("ssm_inherited_value");
        this.finalData["value"] = "";
        break;
    }
    if(smartField.id === "ssm_type"){
      this.finalData["type"] = values.current.value;
    } else {
      this.finalData["value"] = values.current.value;
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
        default:
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
      return ""/* "None".fontcolor("ced4da") */;
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
      const defaultValue = response.data.data.defaultValues;
      const fields = response.data.data.fields;
      Object.keys(defaultValue).map(item => {
        const field = fields.find(element => element.id === item);
        if (!this.unsupportedType.includes(field.simpletype)) {
          const {type, typeFormat} = this.formatType(field.simpletype, field.type)
          if (field) {
            // ToDo : Refactor as a function
            let rawValue: Object = {};
            let displayValue: Object = {};
            if(Array.isArray(defaultValue[item].result)) {
              for (let i = 0; i < defaultValue[item].result.length; i++) {
                const element = defaultValue[item].result[i];
                rawValue[i] = element.value;
                displayValue[i] = element.displayValue;
              }
            } else if (defaultValue[item].result instanceof Object) {
              if(defaultValue[item].result.value && defaultValue[item].result.displayValue)
              {
                rawValue = defaultValue[item].result.value;
                displayValue = defaultValue[item].result.displayValue;
              }
              else {
                rawValue = null;
                displayValue = null;
              }
            }
            result.push({
              label: this.formatLabel(field, fields),
              type: JSON.stringify({type: type, typeFormat: typeFormat}),
              parentValue: defaultValue[item].parentConfigurationValue ? defaultValue[item].parentConfigurationValue : null,
              rawValue: rawValue,
              displayValue: displayValue
            });
          }
        }
      });
      return result;
    }
    return [];
  }

  /**
   * Create the 'label' parents architecture
   * @param field 
   * @param fieldsList 
   */
  protected formatLabel(field, fieldsList) {
    // TODO : Revoir avec la nouvelle structure de données
    // Construct label /w parent architecture
    let constructingLabel = [field.labeltext]
    if (field.parentId) {
      const parentField = fieldsList.find(element => element.id === field.parentId)
      constructingLabel.push(parentField.labeltext)
      this.formatLabel(parentField, fieldsList)
    }
    constructingLabel = constructingLabel.reverse();

    return constructingLabel.join(" / ");
  }

  protected formatType(simpleType, longType) {
    let type = simpleType;
    let typeFormat = '';
    if(type === 'docid'){
      typeFormat = longType.match(/"([^"]*)"/)[1];
    }
    return {type: type, typeFormat: typeFormat}
  }

  protected formatValues() {
    
  }

  protected getDefaultValues(options) {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/defaults/`, {
        params: options.data,
        paramsSerializer: kendo.jQuery.param
      })
      .then(response => {
        console.log(response.data.data);  
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
