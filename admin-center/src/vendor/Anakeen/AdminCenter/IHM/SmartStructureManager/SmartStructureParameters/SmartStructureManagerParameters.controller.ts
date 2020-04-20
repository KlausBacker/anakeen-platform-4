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
  public haveParameters = false;
  public smartForm: object = {};
  public unsupportedType = ["frame", "tab"];
  public $refs!: {
    [key: string]: any;
  };
  public smartFormArrayStructure = {};
  public smartFormArrayValues = {};
  public paramValues = [];
  public finalData = {};
  public paramData = [];

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
  get generateSmartForm() {
    const parametersStructure = [];
    const values = {};
    const parametersRenderOptions = {
      types: {
        enum: {
          useSourceUri: true
        }
      }
    };
    const structureFields = [];
    // Generate dynamic smartform content
    console.log("form", this.paramData);
    for (let i = 0; i < this.paramData.length; i++) {
      const fieldId = this.paramData[i].id;
      const field = this.paramData[i];

      console.log("Try root ", field.id);

      if (field.fieldSet.fieldSet && !this.existsField(field.fieldSet.fieldSet, structureFields)) {
        console.log("appped 2 ", field.fieldSet.fieldSet.id);
        this.appendField(field.fieldSet.fieldSet, structureFields);
      }
      if (!this.existsField(field.fieldSet, structureFields)) {
        console.log("appped 1 ", field.fieldSet.id);
        this.appendField(field.fieldSet, structureFields);
      }
      this.appendField(field, structureFields);
    }

    console.log("struct", structureFields);

    if (false) {
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

        console.log(parameter);

        /*parametersStructure.push({
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
        });*/

        /* if (!["array", "htmltext"].includes(parameter.type)) {
          parametersRenderOptions[parameter.parameterId] = {
            responsiveColumns: [
              {
                number: 3,
                minWidth: "50rem",
                grow: true
              }
            ]
          };
        }*/
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
      structure: structureFields,
      title: "Parameters edition",
      renderOptions: parametersRenderOptions,
      values: values
    };
  }

  public mounted() {
    this.getParameters();
  }
  public ssmFormReady() {}
  public initArrayData(arrayParamId) {
    const children = this.smartFormArrayStructure[arrayParamId].map(child => child.label);
    const colCount = children.length;
    const rowCount = this.smartFormArrayValues[children[0]].length;
    const finalValue = [];

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
        if (
          (Array.isArray(parameter.url) && parameter.url.length > 0 && splitted[1] === "result") ||
          this.finalData[parameter.parameterId].valueType === "advanced_value"
        ) {
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
            const multipleValue = [];
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
      const columns = [];
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
    const formattedValues = [];
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
  public onSave(event, smartElement, requestOptions, customData) {
    const url = `/api/v2/admin/smart-structures/${this.ssName}/update/parameter/`;
    const systemData = requestOptions.getRequestData();

    const fieldValues = systemData.document.attributes;

    console.log(systemData);
    const updatedData = [];
    Object.keys(fieldValues).forEach(fieldId => {
      updatedData.push({
        fieldId: fieldId,
        fieldValue: fieldValues[fieldId]
      });
    });

    console.log(updatedData);
    this.$http
      .put(url, { params: updatedData })
      .then(response => {
        console.log("need relaod");
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      });
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
    const finalConfigValue = [];
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
        const configValue = parentConfigParam[i].map(x => {
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

  protected existsField(field, structureFields): boolean {
    if (field.id === "FIELD_HIDDENS") {
      return true;
    }
    for (let i = 0; i < structureFields.length; i++) {
      if (structureFields[i].name === field.id) {
        return true;
      }
      if (structureFields[i].content) {
        const exists = this.existsField(field, structureFields[i].content);
        if (exists === true) {
          return true;
        }
      }
    }
    return false;
  }

  protected appendField(field, structureFields) {
    console.log("try", field.id, "to", field.fieldSet.id);
    if (field.fieldSet.id === "FIELD_HIDDENS") {
      console.log("push", field.id);
      structureFields.push(this.getFieldData(field));
    } else {
      for (let i = 0; i < structureFields.length; i++) {
        if (structureFields[i].name === field.fieldSet.id) {
          if (!structureFields[i].content) {
            structureFields[i].content = [];
          }
          console.log("push", field.id, "to", structureFields[i].name);
          structureFields[i].content.push(this.getFieldData(field));
        } else if (structureFields[i].content) {
          this.appendField(field, structureFields[i].content);
        }
      }
    }
  }

  protected getFieldData(field) {
    const fieldData: any = {
      name: field.id,
      type: field.type,
      label: field.labelText
    };

    if (field.type === "enum") {
      if (field.format) {
        fieldData.enumUrl = "/api/v2/enumerates/" + field.format + "/";
      }
    }

    if (field._topt && field._topt.multiple === "yes") {
      fieldData.multiple = true;
    }
    switch (field.type) {
      case "enum":
      case "docid":
      case "account":
        if (field.format) {
          fieldData.typeFormat = field.format;
        }
    }
    return fieldData;
  }

  protected formatType(simpleType, longType) {
    const type = simpleType;
    let typeFormat = "";
    if (type === "docid" || type === "enum") {
      typeFormat = longType.match(/"([^"]*)"/)[1];
    }
    return { type, typeFormat };
  }

  protected parseParametersData(response) {
    const result = [];
    if (response && response.data && response.data.data) {
      const paramsValues = response.data.data.paramsValues;
      const paramsFields = response.data.data.params;

      for (let i = 0; i < paramsFields.length; i++) {
        const fieldId = paramsFields[i].id;
        paramsFields[i].value = paramsValues[fieldId];
      }

      console.log(paramsFields);
      this.paramData = paramsFields;
    } else {
      this.paramData = [];
    }
  }
  protected getParameters() {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/parameters/`, {})
      .then(response => {
        this.paramValues = [];
        if (response.data.data.params.length === 0) {
          this.haveParameters = false;
        } else {
          this.haveParameters = true;
        }
        this.parseParametersData(response);
      })
      .catch(response => {
        throw new Error(response);
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

    console.log(data);
    return;
    this.$http
      .put(url, { params: JSON.stringify(data) })
      .then(response => {
        this.finalData = {};
        this.paramValues = [];
        this.smartFormArrayValues = {};
        this.smartFormArrayStructure = {};
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      });
  }
}
