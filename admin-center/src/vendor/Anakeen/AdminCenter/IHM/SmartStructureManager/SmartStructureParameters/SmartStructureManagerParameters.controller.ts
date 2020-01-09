import ankSmartController from "@anakeen/user-interfaces/components/lib/AnkController.esm";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import { Component, Prop, Vue, Watch } from "vue-property-decorator";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);

@Component({
  components: {
    "smart-form": () => AnkSmartForm
  }
})
export default class SmartStructureManagerParametersController extends Vue {
  @Prop({
    default: "",
    type: String
  })
  public ssName;
  public haveParameters: boolean = false;
  public smartForm: object = {};
  public unsupportedType = ["frame", "tab" /* , "array" */];
  public $refs!: {
    [key: string]: any;
  };

  public paramValues = [];
  public finalData = {};
  // {
  //   parameterId: "",
  //   structureId: this.ssName,
  //   value: "",
  //   valueType: "value"
  // };
  protected rawValue;
  protected parentValue;
  protected type;

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.$refs.parametersGridData.kendoDataSource.read();
      this.finalData = {};
    }
  }
  @Watch("paramValues")
  public watchParamValues(newValue) {
    if (Array.isArray(newValue[0])) {
      this.paramValues = newValue[0];
    }
  }
  get generateSmartForm() {
    let enumData = [];
    let parametersStructure = [];
    let parametersEnum = [];
    let values = {};
    let parametersRenderOptions = {};
    // Generate dynamic smartform content
    if (this.paramValues.length) {
      parametersEnum = [];
      this.paramValues.forEach(parameter => {
        // ToDo : Demander à Eric la différence entre 'enum' et 'enumLabel' dans la réponse de l'API
        // ToDo : Faire passer les données d'enum et récup les entrées d'enum associées
        // if (parameter.type === "enum") {
        //   enumData = this.getEnum(this.type.typeFormat);
        // }

        // Manage SmartForm values
        if (parameter.isAdvancedValue) {
          values[parameter.parameterId + "-type"] = "advanced_value";
          values[parameter.parameterId + "-advanced_value"] = parameter.rawValue;
        } else {
          values[parameter.parameterId + "-type"] = "value";
          values[parameter.parameterId + "-value"] = parameter.rawValue;
        }
        // Manage SmartForm renderOptions
        // parametersRenderOptions[parameter.parameterId+"-default_value"] = {
        //   collapse: "collapse"
        // }
        // Generate SmartForm structure
        parametersStructure.push({
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
              name: `${parameter.parameterId}-type`,
              type: "enum"
            },
            {
              display: "read",
              label: "Inherited",
              name: `${parameter.parameterId}-inherited_value`,
              type: "text"
            },
            {
              enumItems: `${enumData}`,
              label: "Value",
              name: `${parameter.parameterId}-value`,
              type: `${parameter.type}`
            },
            {
              label: "Advanced value",
              name: `${parameter.parameterId}-advanced_value`,
              type: "longtext"
            }
          ],
          label: `${parameter.label}`,
          name: `${parameter.parameterId}-default_value`,
          type: "frame"
        });
      });
    }
    return {
      menu: [
        {
          beforeContent: '<div class="fa fa-save" />',
          htmlLabel: "",
          iconUrl: "",
          id: "submit",
          important: false,
          label: "Submit",
          target: "_self",
          type: "itemMenu",
          url: "#action/document.save",
          // url: "#action/ssmanager.save",
          visibility: "visible"
        }
      ],
      structure: parametersStructure,
      title: "Parameters edition",
      renderOptions: {
        fields: parametersRenderOptions
      },
      values: values
    };
  }
  public ssmFormReady() {
    if (this.paramValues) {
      this.paramValues.forEach(parameter => {
        // Prepare finalData model
        this.finalData[parameter.parameterId] = {
          parameterId: parameter.parameterId,
          structureId: this.ssName,
          value: parameter.rawValue,
          valueType: this.$refs.ssmForm.getValue(`${parameter.parameterId}-type`).value
        };
        // Manage which field to show/hide
        this.manageHiddenFields(parameter);
      });
    }
  }
  public manageHiddenFields(parameter) {
     this.$refs.ssmForm.getSmartFields().forEach(sf => {
      const splitted = sf.id.split("-");
      if (
        splitted[0] === parameter.parameterId &&
        splitted[1] !== "type" &&
        splitted[1] !== "default_value" &&
        this.finalData[parameter.parameterId].valueType !== splitted[1]
      ) {
      this.$refs.ssmForm.hideSmartField(`${splitted[0]}-${splitted[1]}`);
    }
  });
  }
  public ssmFormChange(e, smartStructure, smartField, values, index) {
    const smartForm = this.$refs.ssmForm;
    if (this.paramValues) {
      this.paramValues.forEach(parameter => {
        switch (smartForm.getValue(`${parameter.parameterId}-type`).value) {
          case "inherited":
            smartForm.hideSmartField(`${parameter.parameterId}-advanced_value`);
            smartForm.hideSmartField(`${parameter.parameterId}-value`);
            smartForm.showSmartField(`${parameter.parameterId}-inherited_value`);
            this.finalData[parameter.parameterId].valueType = smartForm.getValue(`${parameter.parameterId}-type`).value;
            this.finalData[parameter.parameterId].value =
              smartForm.getValue(`${parameter.parameterId}-inherited_value`) !== null
                ? smartForm.getValue(`${parameter.parameterId}-inherited_value`).value
                : "";
            break;
          case "value":
            smartForm.hideSmartField(`${parameter.parameterId}-advanced_value`);
            smartForm.showSmartField(`${parameter.parameterId}-value`);
            smartForm.hideSmartField(`${parameter.parameterId}-inherited_value`);
            this.finalData[parameter.parameterId].valueType = smartForm.getValue(`${parameter.parameterId}-type`).value;
            this.finalData[parameter.parameterId].value =
            smartForm.getValue(`${parameter.parameterId}-value`) !== null
              ? smartForm.getValue(`${parameter.parameterId}-value`).value
              : "";
            break;
          case "advanced_value":
            smartForm.showSmartField(`${parameter.parameterId}-advanced_value`);
            smartForm.hideSmartField(`${parameter.parameterId}-value`);
            smartForm.hideSmartField(`${parameter.parameterId}-inherited_value`);
            this.finalData[parameter.parameterId].valueType = smartForm.getValue(`${parameter.parameterId}-type`).value;
            this.finalData[parameter.parameterId].value =
              smartForm.getValue(`${parameter.parameterId}-advanced_value`) !== null
                ? smartForm.getValue(`${parameter.parameterId}-advanced_value`).value
                : "";
            break;
          case "no_value":
            this.$refs.ssmForm.hideSmartField(`${parameter.parameterId}-advanced_value`);
            this.$refs.ssmForm.hideSmartField(`${parameter.parameterId}-value`);
            this.$refs.ssmForm.hideSmartField(`${parameter.parameterId}-inherited_value`);
            this.finalData[parameter.parameterId].valueType = smartForm.getValue(`${parameter.parameterId}-type`).value;
            this.finalData[parameter.parameterId].value = "";
            break;
        }
      });
    }
    // console.log("FINALDATA", this.finalData);
  }
  public formClickMenu(e, se, params) {
    if (params.eventId === "document.save") {
      this.updateData(this.finalData);
    }
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
      return "" /* "None".fontcolor("ced4da") */;
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
          // ToDo : Refactor as multiple functions
          if (param) {
            const configParam = paramsValues[item].configurationParameter;
            const type = param.type;
            let isAdvancedValue = false;
            let rawValue: object = {};
            let displayValue: object = {};
            if (Array.isArray(paramsValues[item].result)) {
              for (let i = 0; i < paramsValues[item].result.length; i++) {
                const element = paramsValues[item].result[i];
                if (configParam && typeof configParam !== "undefined" && configParam !== element.value) {
                  rawValue[i] = configParam;
                  isAdvancedValue = true;
                } else {
                  rawValue[i] = element.value;
                }
                displayValue[i] = element.displayValue;
              }
            } else if (paramsValues[item].result instanceof Object) {
              if (paramsValues[item].result.value && paramsValues[item].result.displayValue) {
                if (configParam && typeof configParam !== "undefined" && configParam !== paramsValues[item].result.value) {
                  rawValue = configParam;
                  isAdvancedValue = true;
                } else {
                  rawValue = paramsValues[item].result.value;
                }
                displayValue = paramsValues[item].result.displayValue;
              } else {
                rawValue = null;
                displayValue = null;
              }
            }
            result.push({
              displayValue,
              label: param.labelText,
              parameterId: item,
              parentValue: paramsValues[item].parentConfigurationValue
                ? paramsValues[item].parentConfigurationValue
                : null,
              rawValue,
              type,
              isAdvancedValue
            });
          }
        } else {
          // ToDo : Manage Error
        }
      });
      this.paramValues.push(result);
      return result;
    }
    return [];
  }
  protected formatType(simpleType, longType) {
    const type = simpleType;
    let typeFormat = "";
    if (type === "docid" || type === "enum") {
      typeFormat = longType.match(/"([^"]*)"/)[1];
    }
    return { type, typeFormat };
  }
  protected getParameters(options) {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/parameters/`, {
        params: options.data,
        paramsSerializer: kendo.jQuery.param
      })
      .then(response => {
        this.paramValues = [];
        if (Array.isArray(response.data.data.params)) {
          this.haveParameters = false;
        } else {
          this.haveParameters = true;
        }
        options.success(response);
      })
      .catch(response => {
        options.error(response);
      });
    return [];
  }
  protected getEnum(enumerate) {
    const returnVal = [];
    this.$http
      .get(`/api/v2/admin/enumdata/${enumerate}`)
      .then(response => {
        if (response.status === 200 && response.statusText === "OK") {
          response.data.data.forEach(element => {
            returnVal.push({
              key: element.key,
              label: element.label
            });
          });
        } else {
          throw new Error(response.data);
        }
      })
      .catch(response => {
        console.error(response);
      });

    return returnVal;
  }
  private updateData(data) {
    const url = `/api/v2/admin/smart-structures/${this.ssName}/update/parameter/`;
    this.$http
      .put(url, { params: JSON.stringify(data) })
      .then(response => {
        this.$refs.parametersGridData.kendoDataSource.read();
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      });
  }
}
