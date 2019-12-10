import ankSmartController from "@anakeen/user-interfaces/components/lib/AnkController";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import VModal from "vue-js-modal";
import { Component, Prop, Vue, Watch} from "vue-property-decorator";

Vue.use(VModal);
Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);

@Component({
  components: {
    "smart-form": AnkSmartForm,
  }
})
export default class SmartStructureManagerParametersController extends Vue {
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
  public finalData = {
    parameterId: "",
    structureId: this.ssName,
    value: "",
    valueType: "value",
  }
  public editWindow = {
    title: "",
    width: "50%"
  };
  // ToDo
  protected rawValue;
  protected parentValue;
  protected type;

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.$refs.parametersGridContent.kendoWidget().dataSource.read();
      this.finalData.structureId = newValue;
    }
  }

  public onEditClick(e) {
    const row = $(e.target).closest("tr")[0];
    this.rawValue = row.children[2].innerText;
    this.parentValue = row.children[1].textContent;
    this.type = row.children[4].textContent;
    this.finalData.parameterId = row.children[5].innerText
    this.finalData.value = this.rawValue;
    this.$modal.show("ssm-modal");
    // In case of enumerate, fetch his data
    const enumData = [];
    console.log("TYPE:", this.type)
    if(this.type === "enum") {
      this.$http
      .get(`/api/v2/admin/enumdata/${this.type.typeFormat}`)
      .then(response => {
        if (response.status === 200 && response.statusText === "OK") {
          response.data.data.forEach(element => {
            enumData.push({
              "key": element.key,
              "label": element.label
            })
          })
        } else {
          throw new Error(response.data);
        }
      })
      .catch(response => {
        console.error(response);
      });
    }
    
    this.$modal.show("ssm-modal");
    this.smartForm = {
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
                  label: "Erase field"
                }
              ],
              label: "Type",
              name: "ssm_type",
              type: "enum"
            },
            {
              display: "read",
              label: "Inherited",
              name: "ssm_inherited_value",
              type: "text",
            },
            {
              enumItems: enumData,
              label: "Value",
              name: "ssm_value",
              type: `${this.type}`
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
        ssm_advanced_value: "",
        ssm_inherited_value: `${this.parentValue}`,
        ssm_type: "value",
        ssm_value: `${this.rawValue}`
      }
    }
  }
  // public showSmartForm() {
    
  // }
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
        this.finalData.valueType =smartForm.getValue("ssm_type").value
        this.finalData.value = smartForm.getValue("ssm_inherited_value").value;
        break;
      case "value":
        smartForm.hideSmartField("ssm_advanced_value");
        smartForm.showSmartField("ssm_value");
        smartForm.hideSmartField("ssm_inherited_value");
        this.finalData.valueType = smartForm.getValue("ssm_type").value;
        this.finalData.value = smartForm.getValue("ssm_value").value;
        break;
      case "advanced_value":
        smartForm.showSmartField("ssm_advanced_value");
        smartForm.hideSmartField("ssm_value");
        smartForm.hideSmartField("ssm_inherited_value");
        this.finalData.valueType = smartForm.getValue("ssm_type").value;
        this.finalData.value = smartForm.getValue("ssm_advanced_value").value;
        break;
      case "no_value":
        this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
        this.$refs.ssmForm.hideSmartField("ssm_value");
        this.$refs.ssmForm.hideSmartField("ssm_inherited_value");
        this.finalData.valueType = smartForm.getValue("ssm_type").value;
        this.finalData.value = "";
        break;
    }
  }
  public formClickMenu(e, se, params) {
    switch (params.eventId) {
      case "ssmanager.cancel":
        this.$modal.hide("ssm-modal");
        break;
      case "ssmanager.save":
        this.updateData(this.finalData);
        break;
    }
  }
  // public beforeEdit(data) {
  //   this.smartForm = data.params.config;
  // }
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
  protected parseParametersData(response) {
    const result = [];
    if (response && response.data && response.data.data) {
      const paramsValues = response.data.data.paramsValues;
      const params = response.data.data.params;
      Object.keys(paramsValues).map(item => {
        const param = params[item];
        if (!this.unsupportedType.includes(param.type)) {
          if (param) {
            const type = param.type;
            // ToDo : Refactor as a function
            let rawValue: object = {};
            let displayValue: object = {};
            if(Array.isArray(paramsValues[item].result)) {
              for (let i = 0; i < paramsValues[item].result.length; i++) {
                const element = paramsValues[item].result[i];
                rawValue[i] = element.value;
                displayValue[i] = element.displayValue;
              }
            } else if (paramsValues[item].result instanceof Object) {
              if(paramsValues[item].result.value && paramsValues[item].result.displayValue)
              {
                rawValue = paramsValues[item].result.value;
                displayValue = paramsValues[item].result.displayValue;
              }
              else {
                rawValue = null;
                displayValue = null;
              }
            }
            result.push({
              displayValue,
              label: param.labelText,
              parameterId: item,
              parentValue: paramsValues[item].parentConfigurationValue ? paramsValues[item].parentConfigurationValue : null,
              rawValue,
              type
            });
          }
          console.log(result);
        } else {
          // ToDo : Manage Error
        }
      });
      return result;
    }
    return [];
  }
  protected formatType(simpleType, longType) {
    const type = simpleType;
    let typeFormat = '';
    if(type === 'docid' || type === 'enum'){
      typeFormat = longType.match(/"([^"]*)"/)[1];
    }
    return {type, typeFormat}
  }
  // protected formatValuesLabels() {
    
  // }
  protected getParameters(options) {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/parameters/`, {
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
  protected getEnum(enumerate){
    const returnVal = [];
    this.$http
    .get(`/api/v2/admin/enumdata/${enumerate}`)
    .then(response => {
      if (response.status === 200 && response.statusText === "OK") {
        response.data.data.forEach(element => {
          returnVal.push({
            key: element.key,
            label: element.label
          })
        })
      } else {
        throw new Error(response.data);
      }
    })
    .catch(response => {
      console.error(response);
    });

    return returnVal;
  }
  protected autoFilterCol(e) {
    e.element.addClass("k-textbox filter-input");
  }
  private updateData(data){
    const url = `/api/v2/admin/smart-structures/${data.structureId}/update/parameter/`;
    this.$http
      .put(url, {params: JSON.stringify(data)})
      .then(response => {
        this.$refs.parametersGridData.kendoDataSource.read();
        this.$modal.hide("ssm-modal");
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      })
  }
}
