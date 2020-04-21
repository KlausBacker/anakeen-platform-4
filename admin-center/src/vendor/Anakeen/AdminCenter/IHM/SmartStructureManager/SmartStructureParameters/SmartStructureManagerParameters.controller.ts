/* eslint-disable @typescript-eslint/no-explicit-any */
import ankSmartController from "@anakeen/user-interfaces/components/lib/AnkController.esm";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import { Component, Prop, Vue, Watch } from "vue-property-decorator";

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
  public structureData: any = {};
  public paramParentValues: any = {};
  public paramValues = {};
  public paramData = [];

  @Watch("ssName")
  public watchSsName(newValue): void {
    if (newValue) this.getParameters();
  }
  get generateSmartForm(): object {
    const parametersRenderOptions = {
      fields: {},
      types: {
        enum: {
          useSourceUri: true,
          editDisplay: "autoCompletion"
        }
      }
    };
    const structureFields = [];
    // Generate dynamic smartform content
    console.log("form", this.paramData);
    for (let i = 0; i < this.paramData.length; i++) {
      const field = this.paramData[i];

      if (field.fieldSet.fieldSet && !this.existsField(field.fieldSet.fieldSet, structureFields)) {
        this.appendField(field.fieldSet.fieldSet, structureFields);
      }
      if (!this.existsField(field.fieldSet, structureFields)) {
        this.appendField(field.fieldSet, structureFields);
      }
      this.appendField(field, structureFields);
    }

    Object.keys(this.paramParentValues).forEach(fieldId => {
      if (this.paramParentValues[fieldId].displayValue) {
        parametersRenderOptions.fields[fieldId] = {
          placeHolder: this.paramParentValues[fieldId].displayValue
        };
      }
    });
    console.log("struct", structureFields);
    console.log("values", this.paramValues);
    console.log("render", parametersRenderOptions);

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
      title: this.structureData.title + " parameters",
      renderOptions: parametersRenderOptions,
      values: this.paramValues
    };
  }

  public mounted(): void {
    this.getParameters();
  }

  public onSave(event, smartElement, requestOptions) {
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
      .then(() => {
        this.getParameters();
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

  protected getFieldData(field): any {
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

  protected parseParametersData(response): void {
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
        if (paramsValues[fieldId].configurationParameter !== null) {
          if (paramsValues[fieldId].result) {
            this.paramValues[fieldId] = paramsValues[fieldId].result;
          }
        } else {
          if (paramsValues[fieldId].parentConfigurationParameter !== null) {
            if (paramsValues[fieldId].result) {
              this.paramParentValues[fieldId] = paramsValues[fieldId].result;
            }
          }
        }
      });

      this.structureData = response.data.data.properties;
      console.log(paramsFields);
      console.log(this.paramValues);
    } else {
      this.paramData = [];
      this.paramValues = {};
    }
  }
  protected getParameters(): void {
    this.$http
      .get(`/api/v2/admin/smart-structures/${this.ssName}/parameters/`, {})
      .then(response => {
        this.haveParameters = response.data.data.params.length !== 0;
        this.parseParametersData(response);
      })
      .catch(response => {
        throw new Error(response);
      });
  }
}
