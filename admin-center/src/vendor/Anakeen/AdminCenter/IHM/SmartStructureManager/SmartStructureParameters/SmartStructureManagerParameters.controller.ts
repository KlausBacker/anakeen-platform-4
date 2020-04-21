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
  public paramValues = {};
  public finalData = {};
  public paramData = [];

  @Watch("ssName")
  public watchSsName(newValue) {
    if (newValue) {
      this.$refs.parametersGridData.kendoDataSource.read();
      this.smartForm = {};
      this.finalData = {};
      // this.paramValues = [];
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

      if (field.fieldSet.fieldSet && !this.existsField(field.fieldSet.fieldSet, structureFields)) {
        this.appendField(field.fieldSet.fieldSet, structureFields);
      }
      if (!this.existsField(field.fieldSet, structureFields)) {
        this.appendField(field.fieldSet, structureFields);
      }
      this.appendField(field, structureFields);
    }

    console.log("struct", structureFields);
    console.log("values", this.paramValues);

    return {
      menu: [
        {
          beforeContent: '<div class="fa fa-save" />',
          htmlLabel: "",
          iconUrl: "",
          id: "submit",
          important: false,
          label: "Record parameters",
          target: "_self",
          type: "itemMenu",
          url: "#action/document.save",
          visibility: "visible"
        }
      ],
      structure: structureFields,
      title: "Parameters edition",
      renderOptions: parametersRenderOptions,
      values: this.paramValues
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
    /*
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

 */
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

  protected appendField(field, structureFields): void {
    if (field.fieldSet.id === "FIELD_HIDDENS") {
      structureFields.push(this.getFieldData(field));
    } else {
      for (let i = 0; i < structureFields.length; i++) {
        if (structureFields[i].name === field.fieldSet.id) {
          if (!structureFields[i].content) {
            structureFields[i].content = [];
          }
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

      this.paramData = paramsFields;

      this.paramValues = {};

      Object.keys(paramsValues).forEach(fieldId => {
        this.paramValues[fieldId] = paramsValues[fieldId]["result"];
      });
      console.log(paramsFields);
      console.log(this.paramValues);
    } else {
      this.paramData = [];
      this.paramValues = {};
    }
  }
  protected getParameters() {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/parameters/`, {})
      .then(response => {
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
}
