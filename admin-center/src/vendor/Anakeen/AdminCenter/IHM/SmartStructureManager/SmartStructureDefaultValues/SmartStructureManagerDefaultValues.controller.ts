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
export default class SmartStructureManagerDefaultValuesController extends Vue {
  @Prop({
    default: "",
    type: String
  })
  public ssName;

  // Components
  public smartForm: object = {};
  public smartFormModal = this.$refs.smartFormModal;
  // Data
  public initialDefVal = {};
  public actualDefValData = {
    rawValue: "",
    displayValue: "",
    parentValue: "",
    type: {
      type: "",
      typeFormat: ""
    }
  };
  public finalData = {
    fieldId: "",
    parentFieldId: "",
    structureId: this.ssName,
    value: "",
    valueType: "value",
    isAdvancedValue: false
  };
  // Config
  public smartFormArrayStructure = {};
  public smartFormArrayValues = {};
  public unsupportedType = ["frame", "tab" /* , "array" */];
  public $refs!: {
    [key: string]: any;
  };
  public showModal = false;
  public isValArray = false;
  public smartFormDisplayManager = {
    ssm_array: "none",
    ssm_value: "write",
    ssm_advanced_value: "write"
  };

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.smartFormArrayStructure = {};
      this.smartFormArrayValues = {};
      this.$refs.defaultGridContent.kendoWidget().dataSource.read();
      this.finalData.structureId = newValue;
    }
  }
  public onEditClick(e) {
    // Get clicked default value data
    const row = $(e.target).closest("tr")[0];
    this.actualDefValData.parentValue = row.children[1].textContent;
    this.actualDefValData.rawValue = row.children[2].innerText;
    this.actualDefValData.displayValue = row.children[3].innerText;
    this.actualDefValData.type = JSON.parse(row.children[4].textContent);
    this.finalData.fieldId = row.children[5].innerText;
    this.finalData.parentFieldId = row.children[6].innerText;
    this.finalData.isAdvancedValue = row.children[7].innerText;
    this.finalData.value = this.getArrayDefaultValue(this.finalData.fieldId);
    this.showModal = true;
    this.showSmartForm();
  }
  /**
   * Prepare last SmartForm's data and create it
   */
  public showSmartForm() {
    // In case of enumerate, fetch his data
    let enumData = [];
    if (this.actualDefValData.type.type === "enum") {
      enumData = this.getEnum(this.actualDefValData.type.typeFormat);
    }
    // In case of array default value, manage array/field display
    if (this.smartFormArrayStructure.hasOwnProperty(this.finalData.fieldId)) {
      this.smartFormDisplayManager.ssm_array = "write";
      this.smartFormDisplayManager.ssm_value = "none";
      this.isValArray = true;
    } else {
      this.smartFormDisplayManager.ssm_array = "none";
      this.smartFormDisplayManager.ssm_value = "write";
      this.isValArray = false;
    }
    // Manage values to display onto the SmartForm
    // @ts-ignore
    if (this.finalData.isAdvancedValue === "true") {
      Object.assign(this.smartFormArrayValues, {
        ssm_advanced_value: {
          displayValue: `${this.actualDefValData.rawValue}`,
          value: `${this.actualDefValData.rawValue}`
        },
        ssm_inherited_value: `${this.actualDefValData.parentValue}`,
        ssm_type: "advanced_value",
        ssm_value: `${this.finalData.value}`
      });
    } else {
      Object.assign(this.smartFormArrayValues, {
        ssm_advanced_value: `${this.actualDefValData.rawValue}`,
        ssm_inherited_value: `${this.actualDefValData.parentValue}`,
        ssm_type: "value",
        ssm_value: {
          displayValue: `${this.actualDefValData.displayValue}`,
          value: `${this.finalData.value}`
        }
      });
    }
    // Manage child/parent data display
    if (this.isValArray === true) {
      this.smartFormDisplayManager.ssm_advanced_value = "none";
    } else {
      this.smartFormDisplayManager.ssm_advanced_value = "write";
    }
    // SmartForm Generation
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
          url: "#action/document.delete",
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
          url: "#action/document.save",
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
              type: "text"
            },
            {
              display: this.smartFormDisplayManager.ssm_value,
              enumItems: enumData,
              label: "Value",
              name: "ssm_value",
              type: `${this.actualDefValData.type.type}`,
              typeFormat: `${this.actualDefValData.type.typeFormat}`
            },
            {
              display: this.smartFormDisplayManager.ssm_advanced_value,
              label: "Advanced value",
              name: "ssm_advanced_value",
              type: "longtext"
            },
            {
              content: this.smartFormArrayStructure[this.finalData.fieldId],
              display: this.smartFormDisplayManager.ssm_array,
              label: "Array",
              name: "ssm_array",
              type: "array"
            }
          ],
          label: "Default value",
          name: "ssm_default_value",
          type: "frame"
        }
      ],
      title: "Edit value form",
      values: this.smartFormArrayValues
    };
  }
  public ssmFormReady() {
    if (this.$refs.ssmForm.getSmartField("ssm_inherited_value")) {
      this.$refs.ssmForm.hideSmartField("ssm_inherited_value");
    }
    // @ts-ignore
    switch (this.smartFormArrayValues.ssm_type) {
      case "advanced_value":
        if (this.$refs.ssmForm.getSmartField("ssm_advanced_value")) {
          this.$refs.ssmForm.showSmartField("ssm_advanced_value");
        }
        if (this.$refs.ssmForm.getSmartField("ssm_value")) {
          this.$refs.ssmForm.hideSmartField("ssm_value");
        }
        break;
      case "value":
        if (this.$refs.ssmForm.getSmartField("ssm_advanced_value")) {
          this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
        }
        if (this.$refs.ssmForm.getSmartField("ssm_value")) {
          this.$refs.ssmForm.showSmartField("ssm_value");
        }
        break;
    }
  }
  public async ssmFormChange(e, smartStructure, smartField, values, index) {
    const smartForm = this.$refs.ssmForm;

    if (smartField.id === "ssm_type") {
      switch (smartForm.getValue("ssm_type").value) {
        case "inherited":
          smartForm.hideSmartField("ssm_advanced_value");
          smartForm.hideSmartField("ssm_value");
          smartForm.hideSmartField("ssm_array");
          smartForm.showSmartField("ssm_inherited_value");
          this.finalData.valueType = smartForm.getValue("ssm_type").value;
          this.finalData.value = smartForm.getValue("ssm_inherited_value").value;
          break;
        case "value":
          smartForm.hideSmartField("ssm_advanced_value");
          smartForm.showSmartField("ssm_value");
          smartForm.showSmartField("ssm_array");
          smartForm.hideSmartField("ssm_inherited_value");
          this.finalData.value = this.getArrayDefaultValue(this.finalData.fieldId);
          this.finalData.valueType = smartForm.getValue("ssm_type").value;
          break;
        case "advanced_value":
          smartForm.showSmartField("ssm_advanced_value");
          smartForm.hideSmartField("ssm_value");
          smartForm.hideSmartField("ssm_array");
          smartForm.hideSmartField("ssm_inherited_value");
          this.finalData.value = smartForm.getValue("ssm_advanced_value").value;
          this.finalData.valueType = smartForm.getValue("ssm_type").value;
          break;
        case "no_value":
          smartForm.hideSmartField("ssm_advanced_value");
          smartForm.hideSmartField("ssm_value");
          smartForm.hideSmartField("ssm_array");
          smartForm.hideSmartField("ssm_inherited_value");
          this.finalData.valueType = smartForm.getValue("ssm_type").value;
          this.finalData.value = "";
          break;
      }
    } else {
      if (this.isValArray) {
        for (let i = 0; i < values.current.length; i++) {
          const newValue = values.current[i].value;
          this.smartFormArrayValues[smartField.id][i] = newValue;
        }
      }

      // @ts-ignore
      else if (smartField.id === "ssm_value") {
        this.finalData.value = values.current.value;
      } else {
        this.finalData.value = smartForm.getValue(`ssm_${this.finalData.valueType}`).value;
      }
      await this.finalFormatValue(this.finalData.fieldId, values.initial.value);
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
  public formClickMenu(e, se, params) {
    switch (params.eventId) {
      case "document.delete":
        this.showModal = false;
        break;
      case "document.save":
        // this.finalFormatValue(this.finalData.fieldId, this.finalData.parentFieldId);
        this.updateData(this.finalData);
        break;
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
  protected parseDefaultValuesData(response) {
    const result = [];
    if (response && response.data && response.data.data) {
      const defaultValues = response.data.data.defaultValues;
      const fields = response.data.data.fields;
      // Browse each default value
      Object.keys(defaultValues).map(item => {
        const defaultVal = defaultValues[item];
        const field = fields.find(element => element.id === item);
        const parentField = fields.find(element => element.id === field.parentId);
        if (!this.unsupportedType.includes(field.simpletype)) {
          const { type, typeFormat } = this.formatType(field.simpletype, field.type);
          // Manage multiple values
          if (parentField.type === "array") {
            this.prepareSmartFormArray(defaultVal, field, parentField);
          }
          if (field) {
            // ToDo : Refactor as multiple functions
            const configDefVal = defaultVal.configurationValue;
            let isAdvancedValue = false;
            let rawValue = "";
            let displayValue;

            if (Array.isArray(configDefVal)) {
              configDefVal.forEach(actualConfigValue => {
                if (typeof actualConfigValue === "object") {
                  if (actualConfigValue.displayValue && actualConfigValue.rawValue) {
                    displayValue = actualConfigValue.displayValue;
                    rawValue = actualConfigValue.rawValue;
                  } else {
                    displayValue = "";
                    rawValue = "";
                  }
                } else {
                  rawValue = actualConfigValue;
                  displayValue = actualConfigValue;
                }
                result.push({
                  displayValue,
                  fieldId: item,
                  parentFieldId: parentField.id,
                  label: this.formatLabel(field, fields),
                  parentValue: defaultVal.parentConfigurationValue ? defaultVal.parentConfigurationValue : null,
                  rawValue,
                  type: JSON.stringify({ type, typeFormat }),
                  isAdvancedValue
                });
              });
              return;
              // } else if (defaultVal.result instanceof Object) {
            } else if (configDefVal instanceof Object) {
              if (defaultVal.result.value && defaultVal.result.displayValue) {
                if (configDefVal && typeof configDefVal !== "undefined" && configDefVal != defaultVal.result.value) {
                  rawValue = configDefVal;
                  isAdvancedValue = true;
                } else {
                  rawValue = defaultVal.result.value;
                }
                displayValue = defaultVal.result.displayValue;
              } else if (defaultVal.result[0] && defaultVal.result[0].value && defaultVal.result[0].displayValue) {
                rawValue = defaultVal.result[0].value;
                displayValue = defaultVal.result[0].displayValue;
              } else {
                if (configDefVal && configDefVal.rawValue && configDefVal.displayValue) {
                  rawValue = configDefVal.rawValue;
                  displayValue = configDefVal.displayValue;
                } else {
                  rawValue = configDefVal;
                  displayValue = configDefVal;
                }
              }
            } else {
              if (configDefVal !== null) {
                rawValue = configDefVal;
                if (Array.isArray(defaultVal.result)) {
                  defaultVal.result.forEach(defValElem => {
                    if (defValElem && defValElem.value && defValElem.displayValue) {
                      if (defValElem.displayValue == configDefVal) {
                        rawValue = defValElem.value;
                        displayValue = defValElem.displayValue;
                      } else if (defValElem.value == configDefVal) {
                        displayValue = defValElem.displayValue;
                      } else {
                        displayValue = configDefVal;
                      }
                    }
                  });
                } else if (defaultVal.result && defaultVal.result.displayValue) {
                  displayValue = defaultVal.result.displayValue;
                } else {
                  displayValue = configDefVal;
                }
              } else {
                if (defaultVal.result && defaultVal.result.displayValue && defaultVal.result.value) {
                  rawValue = defaultVal.result.value;
                  displayValue = defaultVal.result.displayValue;
                } else {
                  rawValue = null;
                  displayValue = null;
                }
              }
            }
            result.push({
              displayValue,
              fieldId: item,
              parentFieldId: parentField.id,
              label: this.formatLabel(field, fields),
              parentValue: defaultVal.parentConfigurationValue ? defaultVal.parentConfigurationValue : null,
              rawValue,
              type: JSON.stringify({ type, typeFormat }),
              isAdvancedValue
            });
          }
        }
      });
      return result;
    }
    return [];
  }

  protected prepareSmartFormArray(defaultVal, field, parentField) {
    // Prepare data and structure
    const { type, typeFormat } = this.formatType(field.simpletype, field.type);
    const column = {
      enumItems: [],
      label: field.id,
      name: field.id,
      type: type,
      typeFormat: typeFormat
    };
    const values = {};
    // Define array if not already done
    if (!this.smartFormArrayStructure[parentField.id]) {
      this.smartFormArrayStructure[parentField.id] = [];
    }
    if (!this.smartFormArrayValues[parentField.id]) {
      this.smartFormArrayValues[parentField.id] = [];
    }

    // In case of enum type, fetch associate data
    if (field.simpletype === "enum") {
      column.enumItems = this.getEnum(typeFormat);
      column.typeFormat = "";
    }
    if (!values[field.id]) {
      values[field.id] = [];
    }
    // if (defaultVal && defaultVal.result && defaultVal.result.length > 0) {
    if (defaultVal && defaultVal.result && Array.isArray(defaultVal.result)) {
      defaultVal.result.forEach(element => {
        if (field.simpleType !== "enum") {
          if (element.value && element.displayValue) {
            values[field.id].push(element.value);
          } else {
            values[field.id].push("");
          }
        }
      });
    } else {
      values[field.id].push("");
    }
    this.smartFormArrayStructure[parentField.id].push(column);
    Object.assign(this.smartFormArrayValues, values);
  }
  protected formatLabel(field, fieldsList) {
    // Construct label /w parent architecture
    let constructingLabel = [field.labeltext];
    if (field.parentId && field.type !== "array") {
      const parentField = fieldsList.find(element => element.id === field.parentId);
      constructingLabel.push(parentField.labeltext);
      this.formatLabel(parentField, fieldsList);
    }
    constructingLabel = constructingLabel.reverse();
    if (constructingLabel[constructingLabel.length - 1] !== null) {
      return constructingLabel.join(" / ");
    }
    return constructingLabel[0];
  }
  protected formatType(simpleType, longType) {
    const type = simpleType;
    let typeFormat = "";
    if (type === "docid" || type === "enum") {
      if (longType.match(/"([^"]*)"/)) {
        typeFormat = longType.match(/"([^"]*)"/)[1];
      } else {
        typeFormat = "";
      }
    }
    return { type, typeFormat };
  }
  protected getDefaultValues(options) {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/defaults/`, {
        params: options.data,
        paramsSerializer: kendo.jQuery.param
      })
      .then(response => {
        this.smartFormArrayStructure = {};
        this.smartFormArrayValues = {};
        this.initialDefVal = response.data.data.defaultValues;
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
  protected autoFilterCol(e) {
    e.element.addClass("k-textbox filter-input");
  }
  private getArrayDefaultValue(fieldId) {
    let value =
      this.smartFormArrayValues.hasOwnProperty(fieldId) && this.smartFormArrayStructure.hasOwnProperty(fieldId)
        ? this.smartFormArrayValues[fieldId]
        : this.actualDefValData.displayValue;
    if (!isNaN(parseInt(value, 10))) {
      value = parseInt(value, 10);
    }
    return value;
  }
  /**
   * Return array values prepared structure for the back 'setDefValue()' : [{colName: value}, {colName: value}, ...]
   * @param fieldId
   */
  private finalFormatValue(fieldId, updatedInitialValue) {
    let actualValue;
    let numberOfLine;
    let formattedValues = [];
    if (this.smartFormArrayStructure.hasOwnProperty(fieldId)) {
      // Arrayception default value
      const children = this.smartFormArrayStructure[fieldId];

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
      actualValue = formattedValues;
    } else if (Array.isArray(this.initialDefVal[fieldId].configurationValue)) {
      // Single array default value
      const initialConfigValues = this.initialDefVal[fieldId].configurationValue;
      const newValue = this.finalData.value;

      // Fetch column's values
      actualValue = [];
      initialConfigValues.forEach(configValue => {
        if (updatedInitialValue == configValue.rawValue) {
          // ToDo : If already updated, stop process
          if (isNaN(parseInt(newValue, 10))) {
            // If newVal string is a real string ...
            actualValue.push(newValue);
          } else {
            // ... or a number
            actualValue.push(parseInt(newValue, 10));
          }
        } else {
          actualValue.push(configValue.rawValue);
        }
      });
    } else {
      if (isNaN(parseInt(this.finalData.value, 10))) {
        // If newVal string is a real string ...
        actualValue = this.finalData.value;
      } else {
        // ... or a number
        actualValue = parseInt(this.finalData.value, 10);
      }
    }
    this.finalData.value = JSON.parse(JSON.stringify(actualValue));
  }
  private updateData(data) {
    const url = `/api/v2/admin/smart-structures/${data.structureId}/update/default/`;
    this.$http
      .put(url, { params: JSON.stringify(data) })
      .then(response => {
        this.$refs.defaultGridData.kendoDataSource.read();
        this.finalData = {
          fieldId: "",
          parentFieldId: "",
          structureId: this.ssName,
          value: "",
          valueType: "value",
          isAdvancedValue: false
        };
        this.showModal = false;
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      });
  }
}
