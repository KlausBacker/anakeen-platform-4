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
  public unsupportedType = ["frame", "tab"];
  public $refs!: {
    [key: string]: any;
  };

  public paramValues = [];
  public finalData = {};
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
        // Manage SmartForm values
        if (parameter.isAdvancedValue) {
          values[parameter.parameterId + "-type"] = "advanced_value";
          values[parameter.parameterId + "-advanced_value"] = parameter.rawValue;
        } else {
          values[parameter.parameterId + "-type"] = "value";
          values[parameter.parameterId + "-value"] = parameter.rawValue;
        }
        // console.log("Param", parameter);
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
          name: `${parameter.parameterId}`,
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
      // console.log("this.finalData[parameter.parameterId].valueType", this.finalData[parameter.parameterId].valueType);
      // console.log("splitted", splitted[1]);
      if (
        splitted[0] === parameter.parameterId &&
        splitted[1] !== "type" &&
        splitted[1] !== "default_value" &&
        typeof splitted[1] !== "undefined" &&
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
      return "";
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
        const paramVal = paramsValues[item];
        const param = params[item];
        // const parentField = param.fieldSet;
        if (!this.unsupportedType.includes(param.type)) {
          if (param) {
            // debugger;
            const configParam = paramVal.configurationParameter;
            const resultParam = paramVal.result;
            const type = param.type;
            const typeFormat = param.format;
            // 
            let isAdvancedValue = false;
            let rawValue;
            let displayValue;

            if (Array.isArray(configParam)) {
              rawValue = [];
              displayValue = [];

                configParam.forEach(actualConfigParam => {
                  if (typeof actualConfigParam === "object") {
                    if (actualConfigParam.displayValue && actualConfigParam.rawValue) {
                      displayValue = actualConfigParam.displayValue;
                      rawValue = actualConfigParam.rawValue;
                    } else {
                      // displayValue = "";
                      // rawValue = "";
                    }
                  } else {
                    rawValue = actualConfigParam;
                    displayValue = actualConfigParam;
                  }

                  if ((param.type === "array" && result[result.length - 1].fieldId !== item) || param.type !== "array") {
                    result.push({
                      displayValue,
                      fieldId: item,
                      // parentFieldId: parentField.id,
                      label: param.labelText,
                      parentValue: paramVal.parentConfigurationValue ? paramVal.parentConfigurationValue : null,
                      rawValue,
                      type: JSON.stringify({ type, typeFormat }),
                      isAdvancedValue,
                    });
                  }

                })
            } else if (configParam instanceof Object) {
              if (resultParam.value && resultParam.displayValue) {
                if (configParam && typeof configParam !== "undefined" && configParam != resultParam.value) {
                  rawValue = configParam;
                } else {
                  rawValue = resultParam.value;
                }
                displayValue = resultParam.displayValue;
              } else if (resultParam[0] && resultParam[0].value && resultParam[0].displayValue) {
                rawValue = resultParam[0].value;
                displayValue = resultParam[0].displayValue;
              } else {
                if (configParam && configParam.rawValue && configParam.displayValue) {
                  rawValue = configParam.rawValue;
                  displayValue = configParam.displayValue;
                } else {
                  rawValue = configParam;
                  displayValue = configParam;
                }
              }
            } else {
              if (configParam !== null) {
                rawValue = configParam;
                if (Array.isArray(resultParam)) {
                  resultParam.forEach(defValElem => {
                    if (defValElem && defValElem.value && defValElem.displayValue) {
                      if (defValElem.displayValue == configParam) {
                        rawValue = defValElem.value;
                        displayValue = defValElem.displayValue;
                      } else if (defValElem.value == configParam) {
                        displayValue = defValElem.displayValue;
                      } else {
                        displayValue = configParam;
                      }
                    }
                  });
                } else if (resultParam && resultParam.displayValue) {
                  displayValue = resultParam.displayValue;
                } else {
                  displayValue = configParam;
                }
              } else {
                if (resultParam && resultParam.hasOwnProperty("displayValue") && resultParam.hasOwnProperty("value")) {
                  rawValue = resultParam.value;
                  displayValue = resultParam.displayValue;
                } else {
                  rawValue = null;
                  displayValue = null;
                }
              }
            }



            // if (Array.isArray(paramsValues[item].result)) {
            //   for (let i = 0; i < paramsValues[item].result.length; i++) {
            //     const element = paramsValues[item].result[i];
            //     if (configParam && typeof configParam !== "undefined" && configParam !== element.value) {
            //       rawValue[i] = configParam;
            //       isAdvancedValue = true;
            //     } else {
            //       rawValue[i] = element.value;
            //     }
            //     displayValue[i] = element.displayValue;
            //   }
            // } else if (paramsValues[item].result instanceof Object) {
            //   if (paramsValues[item].result.value && paramsValues[item].result.displayValue) {
            //     if (
            //       configParam &&
            //       typeof configParam !== "undefined" &&
            //       configParam !== paramsValues[item].result.value
            //     ) {
            //       rawValue = configParam;
            //       isAdvancedValue = true;
            //     } else {
            //       rawValue = paramsValues[item].result.value;
            //     }
            //     displayValue = paramsValues[item].result.displayValue;
            //   } else {
            //     rawValue = null;
            //     displayValue = null;
            //   }
            // } else {

            // }

            if(rawValue && typeof rawValue === "string" && rawValue.includes("::")) {
              isAdvancedValue = true;
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
        console.log("PARAM", response.data.data);
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
