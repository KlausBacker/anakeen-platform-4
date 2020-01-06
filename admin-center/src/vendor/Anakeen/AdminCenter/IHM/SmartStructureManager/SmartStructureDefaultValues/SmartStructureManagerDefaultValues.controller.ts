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
    "smart-form": () => AnkSmartForm,
  }
})
export default class SmartStructureManagerDefaultValuesController extends Vue {
  @Prop({
    default: "",
    type: String
  })
  public ssName;

  public smartForm: object = {};
  public smartFormModal = this.$refs.smartFormModal;
  public showModal = false;
  // Useful, in pair with smartFormDisplayManager, to format or not finalData.value as an array
  public isValArray = false;
  public smartFormDisplayManager = {
    ssm_array: "none",
    ssm_value: "write"
  };
  public unsupportedType = ["frame", "tab"/* , "array" */];
  public $refs!: {
    [key: string]: any;
  };
  public finalData = {
    fieldId: "",
    structureId: this.ssName,
    value: "",
    valueType: "value"
  };
  public smartFormArrayStructure = {};
  public smartFormArrayValues = {};
  // Grid data
  protected rawValue;
  protected displayValue;
  protected parentValue;
  protected type;

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
    // Fetch data from grid
    const row = $(e.target).closest("tr")[0];
    this.rawValue = row.children[2].innerText;
    this.displayValue = row.children[3].innerText;
    this.parentValue = row.children[1].textContent;
    // this.type = {type: 'abcd', typeFormat: 'efgh'}
    this.type = JSON.parse(row.children[4].textContent);
    this.finalData.fieldId = row.children[5].innerText;
    this.finalData.value = this.getArrayDefaultValue(this.finalData.fieldId);
    this.showModal = true;
    this.showSmartForm();
  }
  /**
   * Prepare last SmartForm's data and create it
   */
  public showSmartForm() {
    // SmartForm preparation
    Object.assign(this.smartFormArrayValues, {
      "ssm_advanced_value": "",
      "ssm_inherited_value": `${this.parentValue}`,
      "ssm_type": "value",
      "ssm_value": `${this.displayValue}`,
    });

    // In case of enumerate, fetch his data
    let enumData = [];
    if (this.type.type === "enum") {
      enumData = this.getEnum(this.type.typeFormat);
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
              display: this.smartFormDisplayManager.ssm_value,
              enumItems: enumData,
              label: "Value",
              name: "ssm_value",
              type: `${this.type.type}`,
              typeFormat: `${this.type.typeFormat}`
            },
            {
              label: "Advanced value",
              name: "ssm_advanced_value",
              type: "longtext",
            },
            {
              // ToDo : Change index by parent's id name
              content: this.smartFormArrayStructure[Object.keys(this.smartFormArrayStructure)[0]],
              display: this.smartFormDisplayManager.ssm_array,
              label: "Array",
              name: "ssm_array",
              type: "array",
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
    // console.log("ArrayStructure:", this.smartFormArrayStructure);
    // console.log("ArrayValue:", this.smartFormArrayValues);
    // console.log("===", this.smartFormArrayStructure[Object.keys(this.smartFormArrayStructure)[0]])
    // console.log(JSON.stringify(this.smartForm, null, 2))
  }
  public ssmFormReady() {
    if (this.$refs.ssmForm.getSmartField("ssm_inherited_value")) {
      this.$refs.ssmForm.hideSmartField("ssm_inherited_value");
    }
    if (this.$refs.ssmForm.getSmartField("ssm_advanced_value")) {
      this.$refs.ssmForm.hideSmartField("ssm_advanced_value");
    }
  }
  public ssmFormChange(e, smartStructure, smartField, values, index) {
    const smartForm = this.$refs.ssmForm;

    if(smartField.id === "ssm_type") {
        switch (smartForm.getValue("ssm_type").value) {
          case "inherited":
            smartForm.hideSmartField("ssm_advanced_value");
            smartForm.hideSmartField("ssm_value");
            smartForm.hideSmartField("ssm_array");
            smartForm.showSmartField("ssm_inherited_value");
            this.finalData.valueType = smartForm.getValue("ssm_type").value;
            console.log(smartForm.getValue("ssm_inherited_value"));
            this.finalData.value = smartForm.getValue("ssm_inherited_value").value;
            this.isValArray = false;
            break;
          case "value":
            smartForm.hideSmartField("ssm_advanced_value");
            smartForm.showSmartField("ssm_value");
            smartForm.showSmartField("ssm_array");
            smartForm.hideSmartField("ssm_inherited_value");
            this.finalData.value = this.getArrayDefaultValue(this.finalData.fieldId);
            this.finalData.valueType = smartForm.getValue("ssm_type").value;
            this.isValArray = true;
            break;
          case "advanced_value":
            smartForm.showSmartField("ssm_advanced_value");
            smartForm.hideSmartField("ssm_value");
            smartForm.hideSmartField("ssm_array");
            smartForm.hideSmartField("ssm_inherited_value");
            this.finalData.value = smartForm.getValue("ssm_advanced_value").value;
            this.finalData.valueType = smartForm.getValue("ssm_type").value;
            this.isValArray = false;
            break;
          case "no_value":
            smartForm.hideSmartField("ssm_advanced_value");
            smartForm.hideSmartField("ssm_value");
            smartForm.hideSmartField("ssm_array");
            smartForm.hideSmartField("ssm_inherited_value");
            this.finalData.valueType = smartForm.getValue("ssm_type").value;
            this.finalData.value = "";
            this.isValArray = false;
            break;
        }
        console.log(this.finalData);
    } else {
      if (this.isDefValMultiple()) {
        for (let i = 0; i < values.current.length; i++) {
          const newValue = values.current[i].value;
          this.smartFormArrayValues[smartField.id][i] = newValue;
        }
      } else {
        this.finalData.value = smartForm.getValue("ssm_value").value;
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
  public formClickMenu(e, se, params) {
    switch (params.eventId) {
      case "document.delete":
        this.showModal = false;
        break;
      case "ssmanager.save":
        this.formatArrayFinalValue(this.finalData.fieldId);
        this.updateData(this.finalData);
        this.smartForm = {};
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
    // console.log(response.data.data);
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
            // console.log("MultiplePreparation -> IS_ARRAY");
            this.prepareSmartFormArray(defaultVal, field, parentField);
          }
          if (field) {
            // ToDo : Refactor as a function
            let rawValue: object = {};
            let displayValue: object = {};
            if (Array.isArray(defaultValues[item].result)) {
              // console.log("parsing -> ARRAY");
              for (let i = 0; i < defaultValues[item].result.length; i++) {
                const element = defaultValues[item].result[i];
                // rawValue[i] = element.value;
                // displayValue[i] = element.displayValue;
                result.push({
                  displayValue: element.displayValue,
                  fieldId: item,
                  label: this.formatLabel(field, fields),
                  parentValue: defaultValues[item].parentConfigurationValue
                    ? defaultValues[item].parentConfigurationValue
                    : null,
                  rawValue: element.value,
                  type: JSON.stringify({ type, typeFormat })
                });
              }
              return;
            } else if (defaultValues[item].result instanceof Object) {
              // console.log("parsing -> OBJECT");
              if (defaultValues[item].result.value && defaultValues[item].result.displayValue) {
                rawValue = defaultValues[item].result.value;
                displayValue = defaultValues[item].result.displayValue;
              } else {
                // console.log("parsing -> NO_VAL");
                rawValue = null;
                displayValue = null;
              }
            }
            result.push({
              displayValue,
              fieldId: item,
              label: this.formatLabel(field, fields),
              parentValue: defaultValues[item].parentConfigurationValue
                ? defaultValues[item].parentConfigurationValue
                : null,
              rawValue,
              type: JSON.stringify({ type, typeFormat })
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
      "enumItems": [],
      "label": field.id,
      "name": field.id,
      "type": type,
      "typeFormat": typeFormat,
    };
    const values = {};
    // debugger;
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

    if (defaultVal && defaultVal.result && defaultVal.result.length > 0) {
      if (!values[field.id]) {
        values[field.id] = [];
      }
      defaultVal.result.forEach(element => {
        if (field.simpleType !== "enum") {
          if (element.value && element.displayValue) {
            values[field.id].push(element.value);
          } else {
            values[field.id].push("");
          }
        }
      });
      this.smartFormArrayStructure[parentField.id].push(column);
      Object.assign(this.smartFormArrayValues, values);
    }
  }
  protected formatLabel(field, fieldsList) {
    // TODO : Revoir avec la nouvelle structure de données
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
    let typeFormat = '';
    if (type === "docid" || type === "enum") {
      typeFormat = longType.match(/"([^"]*)"/)[1];
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
  private isDefValMultiple() {
    if (this.isValArray && this.smartFormDisplayManager.ssm_array === "write") {
      return true;
    }
    return false;
  }
  private getArrayDefaultValue(fieldId) {
    const value = this.smartFormArrayValues.hasOwnProperty(fieldId)
      ? this.smartFormArrayValues[fieldId]
      : this.rawValue;
    return value;
  }
  /**
   * Return array values prepared structure for the back 'setDefValue()' : [{colName: value}, {colName: value}, ...]
   * @param fieldId
   */
  private formatArrayFinalValue(fieldId) {
    let actualValue;

    if (this.smartFormArrayStructure.hasOwnProperty(fieldId)) {
      let numberOfLine;
      let formattedValues = [];
      // Get childrens as they are column's name
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
    } else {
      actualValue = this.finalData.value;
    }
    this.finalData.value = JSON.parse(JSON.stringify(actualValue));
  }
  private updateData(data) {
    console.log("BeforeSendData", JSON.stringify(data));
    const url = `/api/v2/admin/smart-structures/${data.structureId}/update/default/`;
    this.$http
      .put(url, { params: JSON.stringify(data) })
      .then(response => {
        this.$refs.defaultGridData.kendoDataSource.read();
        this.showModal = false;
      })
      .catch(response => {
        console.error("UpdateDataResError", response);
      });
  }
}
