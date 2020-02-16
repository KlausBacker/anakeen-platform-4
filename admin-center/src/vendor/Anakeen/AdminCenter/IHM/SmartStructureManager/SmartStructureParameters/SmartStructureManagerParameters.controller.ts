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
  public smartFormArrayStructure = {};
  public smartFormArrayValues = {};
  public paramValues = [];
  public finalData = {};

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.$refs.parametersGridData.kendoDataSource.read();
      this.smartForm = {};
      this.finalData = {};
      this.paramValues = [];
      this.smartFormArrayValues = {};
      this.smartFormArrayStructure = {};
    }
  }
  @Watch("paramValues")
  public watchParamValues(newValue) {
    if (Array.isArray(newValue[0])) {
      this.paramValues = newValue[0];
    }
  }
  get generateSmartForm() {
    let parametersStructure = [];
    let values = {};
    let parametersRenderOptions = {};
    // Generate dynamic smartform content
    if (this.paramValues.length) {
      this.paramValues.forEach(parameter => {
        // Manage SmartForm values
        values[parameter.parameterId + "-result"] = "<ul>";
        if (parameter.isAdvancedValue) {
          values[parameter.parameterId + "-type"] = "advanced_value";
          values[parameter.parameterId + "-advanced_value"] = parameter.rawValue;
          values[parameter.parameterId + "-value"] = parameter.displayValue;
          if (parameter.displayValue.length > 0) {
            for (let i = 0; i < parameter.displayValue.length; i++) {
              values[
                parameter.parameterId + "-result"
              ] += `<li><b>${parameter.displayValue[i].displayValue}</b> - ${parameter.displayValue[i].value}</li>`;
            }
          }
        } else {
          values[parameter.parameterId + "-type"] = "value";
          if (parameter.type === "array") {
            Object.keys(this.smartFormArrayStructure[parameter.parameterId]).forEach(key => {
              const childId = this.smartFormArrayStructure[parameter.parameterId][key].name;
              values[childId] = this.smartFormArrayValues[childId];
            });
          } else {
            if (parameter.rawValue !== null && parameter.rawValue.length > 0) {
              if (parameter.isMultiple) {
                if (!Array.isArray(values[parameter.parameterId])) {
                  values[parameter.parameterId + "-value"] = [];
                }
                for (let i = 0; i < parameter.displayValue.length; i++) {
                  values[parameter.parameterId + "-value"].push({
                    displayValue: parameter.displayValue[i],
                    value: parameter.rawValue[i]
                  });
                  values[
                    parameter.parameterId + "-result"
                  ] += `<li><a href="${window.location.origin}${parameter.url[i]}">${parameter.displayValue[i]}</a></li>`;
                }
              } else {
                values[parameter.parameterId + "-value"] = parameter.rawValue;
              }
            }
            values[parameter.parameterId + "-result"] += "</ul>";
          }
        }
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
              content: this.smartFormArrayStructure[parameter.parameterId],
              enumItems: parameter.enumData,
              label: "Value",
              name: `${parameter.parameterId}-value`,
              type: parameter.type,
              typeFormat: parameter.typeFormat,
              multiple: parameter.isMultiple
            },
            {
              label: "Advanced value",
              name: `${parameter.parameterId}-advanced_value`,
              type: "longtext"
            },
            {
              display: "read",
              label: "Result",
              name: `${parameter.parameterId}-result`,
              type: "htmltext"
            }
          ],
          label: parameter.label,
          name: `${parameter.parameterId}`,
          type: "frame"
        });

        if (!["array", "htmltext"].includes(parameter.type)) {
          parametersRenderOptions[parameter.parameterId] = {
            responsiveColumns: [
              {
                number: 3,
                minWidth: "50rem",
                grow: true
              }
            ]
          };
        }
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
  public mounted() {
    this.$refs.parametersGridData.kendoDataSource.read();
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
        // If param is array's child
        if (Object.keys(this.smartFormArrayStructure).includes(parameter.parameterId)) {
          this.finalData[parameter.parameterId].value = this.initArrayData(parameter.parameterId);
        }
        // Manage which field to show/hide
        this.manageHiddenFields(parameter);
      });
    }
  }
  public initArrayData(arrayParamId) {
    const children = this.smartFormArrayStructure[arrayParamId].map(child => child.label);
    const colCount = children.length;
    const rowCount = this.smartFormArrayValues[children[0]].length;
    let finalValue = [];

    for (let i = 0; i < rowCount; i++) {
      finalValue[i] = [];

      for (let j = 0; j < colCount; j++) {
        const child = children[j];
        finalValue[i].push({
          [child]: this.smartFormArrayValues[child][i]
        });
      }
    }
    return JSON.parse(JSON.stringify(finalValue));
  }
  public manageHiddenFields(parameter) {
    this.$refs.ssmForm.getSmartFields().forEach(sf => {
      const splitted = sf.id.split("-");
      if (
        splitted[0] === parameter.parameterId &&
        splitted[1] !== "type" &&
        typeof splitted[1] !== "undefined" &&
        this.finalData[parameter.parameterId].valueType !== splitted[1]
      ) {
        this.$refs.ssmForm.hideSmartField(`${splitted[0]}-${splitted[1]}`);
        if ((Array.isArray(parameter.url) && parameter.url.length > 0 && splitted[1] === "result") || this.finalData[parameter.parameterId].valueType === "advanced_value") {
          this.$refs.ssmForm.showSmartField(`${splitted[0]}-result`);
        }
      }
    });
  }
  public ssmFormChange(e, smartStructure, smartField, values, index) {
    const smartForm = this.$refs.ssmForm;
    const paramField = smartField.id.split("-")[0];

    if (this.smartFormArrayStructure) {
      if (smartField.id.includes("-type")) {
        switch (smartForm.getValue(smartField.id).value) {
          case "inherited":
            smartForm.hideSmartField(`${paramField}-advanced_value`);
            smartForm.hideSmartField(`${paramField}-value`);
            smartForm.showSmartField(`${paramField}-inherited_value`);
            this.finalData[paramField].valueType = smartForm.getValue(`${paramField}-type`).value;
            this.finalData[paramField].value =
              smartForm.getValue(`${paramField}-inherited_value`) !== null
                ? smartForm.getValue(`${paramField}-inherited_value`).value
                : "";
            break;
          case "value":
            smartForm.hideSmartField(`${paramField}-advanced_value`);
            smartForm.showSmartField(`${paramField}-value`);
            smartForm.hideSmartField(`${paramField}-inherited_value`);
            this.finalData[paramField].valueType = smartForm.getValue(`${paramField}-type`).value;
            this.finalData[paramField].value =
              typeof smartForm.getValue(`${paramField}-value`) !== "undefined"
                ? smartForm.getValue(`${paramField}-value`).value
                : "";
            break;
          case "advanced_value":
            smartForm.showSmartField(`${paramField}-advanced_value`);
            smartForm.hideSmartField(`${paramField}-value`);
            smartForm.hideSmartField(`${paramField}-inherited_value`);
            this.finalData[paramField].valueType = smartForm.getValue(`${paramField}-type`).value;
            this.finalData[paramField].value =
              smartForm.getValue(`${paramField}-advanced_value`) !== null
                ? smartForm.getValue(`${paramField}-advanced_value`).value
                : "";
            break;
          case "no_value":
            this.$refs.ssmForm.hideSmartField(`${paramField}-advanced_value`);
            this.$refs.ssmForm.hideSmartField(`${paramField}-value`);
            this.$refs.ssmForm.hideSmartField(`${paramField}-inherited_value`);
            this.finalData[paramField].valueType = smartForm.getValue(`${paramField}-type`).value;
            this.finalData[paramField].value = "";
            break;
        }
      } else {
        // If value is an array
        if (this.smartFormArrayValues.hasOwnProperty(paramField)) {
          this.formatFinalArrayValue(paramField, values.current);
          // If value isn't an array
        } else if (smartField.id.includes("-value")) {
          const paramInitValues = this.paramValues.find(param => param.parameterId === paramField);
          if (paramInitValues.isMultiple === true) {
            let multipleValue = [];
            values.current.forEach(value => {
              multipleValue.push(value.value);
            });
            this.finalData[paramField].value = multipleValue;
          } else {
            this.finalData[paramField].value = smartForm.getValue(`${paramField}-value`).value;
          }
        } else if (smartField.id.includes("-advanced_value")) {
          this.finalData[paramField].value = smartForm.getValue(`${paramField}-advanced_value`).value;
        } else if (smartField.id.includes("-no_value")) {
          this.finalData[paramField].value = "";
        }
      }
    }
  }
  public ssmArrayChange(e, smartElement, smartField, type, options) {
    if (type === "removeLine") {
      let columns = [];
      // Get column's name
      Object.values(this.smartFormArrayStructure).forEach(values => {
        Object.values(values).forEach(element => {
          columns.push(element.label);
        });
      });
      // Remove the specific value
      Object.keys(this.smartFormArrayValues).forEach(key => {
        if (columns.includes(key)) {
          this.smartFormArrayValues[key].splice(options, 1);
        }
      });
    }
  }
  public formatFinalArrayValue(paramField, values) {
    let actualValue;
    let numberOfLine;
    let arrayFieldToUpdate = null;
    let formattedValues = [];
    // Find parent array field
    Object.keys(this.smartFormArrayStructure).forEach(parentParamField => {
      if (arrayFieldToUpdate === null) {
        this.smartFormArrayStructure[parentParamField].forEach(child => {
          if (child.label === paramField) {
            arrayFieldToUpdate = parentParamField;
          }
        });
      }
    });
    // Store updated value
    for (let i = 0; i < values.length; i++) {
      const newValue = values[i].value;
      this.smartFormArrayValues[paramField][i] = newValue;
    }
    const children = this.smartFormArrayStructure[arrayFieldToUpdate];

    // Fetch column's values
    actualValue = [];
    children.forEach(child => {
      actualValue.push(this.smartFormArrayValues[child.name]);
    });

    // Format
    numberOfLine = actualValue[0].length;
    for (let i = 0; i < numberOfLine; i++) {
      formattedValues[i] = [];
      for (let j = 0; j < children.length; j++) {
        const childName = children[j].name;
        formattedValues[i].push({ [childName]: actualValue[j][i] });
      }
    }
    if (formattedValues.length !== 0) {
      actualValue = formattedValues;
    } else {
      actualValue = "";
    }

    this.finalData[arrayFieldToUpdate].value = JSON.parse(JSON.stringify(actualValue));
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
      const paramsFields = response.data.data.params;
      Object.keys(paramsValues).map(item => {
        const paramVal = paramsValues[item];
        const param = paramsFields[item];
        const parentField = param.fieldSet;
        if (!this.unsupportedType.includes(param.type)) {
          if (param) {
            const configParam = paramVal.configurationParameter;
            const resultParam = paramVal.result;
            const type = param.type;
            const typeFormat = param.format;
            let isAdvancedValue = false;
            let isMultiple = false;
            let rawValue;
            let displayValue;
            let url = null;
            let enumData = [];

            if (param.hasOwnProperty("options") && param.options.includes("multiple=yes")) {
              isMultiple = true;
            }
            if (parentField.type === "array") {
              const parentConfigParam = paramsValues[parentField.id].configurationParameter;
              this.prepareSmartFormArray(paramVal, param, parentField.id, parentConfigParam);
            } else {
              if (Array.isArray(configParam)) {
                rawValue = [];
                displayValue = [];
                url = [];

                if (isMultiple === true) {
                  resultParam.forEach(actualResultValue => {
                    if (typeof actualResultValue === "object") {
                      if (actualResultValue.displayValue && actualResultValue.value) {
                        if (actualResultValue.hasOwnProperty("displayValue"))
                          displayValue.push(actualResultValue.displayValue);
                        if (actualResultValue.hasOwnProperty("value")) rawValue.push(actualResultValue.value);
                        if (actualResultValue.hasOwnProperty("url")) url.push(actualResultValue.url);
                      } else {
                        displayValue.push(null);
                        rawValue.push(null);
                      }
                    } else {
                      rawValue.push(actualResultValue);
                      displayValue.push(actualResultValue);
                    }
                  });
                } else if (type !== "array") {
                  configParam.forEach(actualConfigValue => {
                    if (typeof actualConfigValue === "object") {
                      if (actualConfigValue.displayValue && actualConfigValue.rawValue) {
                        displayValue = actualConfigValue.displayValue;
                        rawValue = actualConfigValue.rawValue;
                      } else {
                        displayValue = null;
                        rawValue = null;
                      }
                    } else {
                      rawValue = actualConfigValue;
                      displayValue = actualConfigValue;
                    }
                  });
                }
              } else if (configParam instanceof Object) {
                if (resultParam.value && resultParam.displayValue) {
                  if (configParam && typeof configParam !== "undefined" && configParam != resultParam.value) {
                    rawValue = configParam;
                  } else {
                    rawValue = resultParam.value;
                  }
                  displayValue = resultParam.displayValue;
                } else if (Array.isArray(resultParam) && resultParam[0].value && resultParam[0].displayValue) {
                  resultParam.forEach(singleResult => {});

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
                    displayValue = [];
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
                  if (
                    resultParam &&
                    resultParam.hasOwnProperty("displayValue") &&
                    resultParam.hasOwnProperty("value")
                  ) {
                    rawValue = resultParam.value;
                    displayValue = resultParam.displayValue;
                  } else {
                    rawValue = null;
                    displayValue = null;
                  }
                }
              }

              if (rawValue && typeof rawValue === "string" && rawValue.includes("::")) {
                displayValue = resultParam;
                isAdvancedValue = true;
              }

              if (type === "enum") {
                enumData = this.getEnum(typeFormat);
              }
              result.push({
                displayValue,
                label: param.labelText,
                parameterId: item,
                parentValue: paramsValues[item].parentConfigurationValue
                  ? paramsValues[item].parentConfigurationValue
                  : null,
                rawValue,
                url,
                type,
                typeFormat,
                enumData,
                isAdvancedValue,
                isMultiple
              });
            }
          }
        }
      });
      this.paramValues.push(result);
      return result;
    }
    return [];
  }
  protected prepareSmartFormArray(paramVal, field, parentId, parentConfigParam) {
    // Prepare data and structure
    const column = {
      enumItems: [],
      label: field.id,
      name: field.id,
      type: field.type,
      typeFormat: field.format
    };
    const values = {};
    let finalConfigValue = [];
    // Define array if not already done
    if (!this.smartFormArrayStructure[parentId]) {
      this.smartFormArrayStructure[parentId] = [];
    }
    if (!this.smartFormArrayValues[parentId]) {
      this.smartFormArrayValues[parentId] = [];
    }

    // In case of enum type, fetch associate data
    if (field.simpletype === "enum") {
      column.enumItems = this.getEnum(field.format);
      column.typeFormat = "";
    }
    if (!values[field.id]) {
      values[field.id] = [];
    }

    if (parentConfigParam !== null && parentConfigParam != "") {
      for (let i = 0; i < parentConfigParam.length; i++) {
        let configValue = parentConfigParam[i].map(x => {
          if (x.hasOwnProperty(field.id) === true) {
            return x[field.id];
          }
        });
        // Remove undefined/null values
        finalConfigValue.push(
          configValue.filter(el => {
            return el != null;
          })
        );
      }
    }
    finalConfigValue.forEach(element => {
      values[field.id].push(element[0]);
    });
    this.smartFormArrayStructure[parentId].push(column);
    Object.assign(this.smartFormArrayValues, values);
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
            if (element.key !== ".extendable") {
              returnVal.push({
                key: element.key,
                label: element.label
              });
            }
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
        this.finalData = {};
        this.paramValues = [];
        this.smartFormArrayValues = {};
        this.smartFormArrayStructure = {};
        this.$refs.parametersGridData.kendoDataSource.read();
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      });
  }
}
