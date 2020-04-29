/* eslint-disable @typescript-eslint/no-explicit-any */

import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import { Component, Prop, Mixins, Watch } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

@Component({
  components: {
    "smart-form": (): any => AnkSmartForm
  }
})
export default class SmartStructureManagerParametersController extends Mixins(AnkI18NMixin) {
  @Prop({
    default: "",
    type: String
  })
  public ssName;
  public haveParameters = false;
  public smartForm: object = {};
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
        },
        frame: {
          responsiveColumns: [
            {
              number: 2,
              minWidth: "90rem",
              maxWidth: "130rem",
              grow: true
            },
            {
              number: 3,
              minWidth: "130rem",
              maxWidth: null,
              grow: true
            }
          ]
        }
      }
    };
    const structureFields = [];
    const formParamValues = {};
    // Generate dynamic smartform content
    for (let i = 0; i < this.paramData.length; i++) {
      const field = this.paramData[i];
      const fieldId = this.paramData[i]["id"];

      if (this.paramValues[fieldId] && this.paramValues[fieldId].isComputed) {
        field.access = 1;
      }

      if (field.fieldSet.fieldSet && !this.existsField(field.fieldSet.fieldSet, structureFields)) {
        this.appendField(field.fieldSet.fieldSet, structureFields);
      }
      if (!this.existsField(field.fieldSet, structureFields)) {
        this.appendField(field.fieldSet, structureFields);
      }
      this.appendField(field, structureFields);
    }

    Object.keys(this.paramValues).forEach(fieldId => {
      let description = "";
      let overDisplayValue = "";
      const paramValue = this.paramValues[fieldId];

      formParamValues[fieldId] = paramValue.result;

      if (paramValue.inheritedValue === true) {
        if (!parametersRenderOptions.fields[fieldId]) {
          parametersRenderOptions.fields[fieldId] = {};
        }
        description += this.$t("AdminCenterSmartStructure.This is the inherited value.") + " ";
      }

      if (paramValue.overrideValue) {
        if (!paramValue.inArray) {
          if (!parametersRenderOptions.fields[fieldId]) {
            parametersRenderOptions.fields[fieldId] = {};
          }
          parametersRenderOptions.fields[fieldId].buttons = [
            {
              title: this.$t("AdminCenterSmartStructure.Reset to inherit value"),
              htmlContent: "R",
              url: "#action/inheritvalue:" + fieldId
            }
          ];
        }

        if (paramValue.overrideValue.displayValue) {
          overDisplayValue = paramValue.overrideValue.displayValue;
        } else if (Array.isArray(paramValue.overrideValue)) {
          overDisplayValue = paramValue.overrideValue
            .map(val => {
              if (Array.isArray(val)) {
                return val
                  .map(val => {
                    return val.displayValue;
                  })
                  .join(" - ");
              } else {
                return val.displayValue;
              }
            })
            .join(", ");
        }

        description += `${this.$t("AdminCenterSmartStructure.he inherited value")} "${overDisplayValue}" ${this.$t(
          "AdminCenterSmartStructure.is overrided."
        )} `;
      }

      if (paramValue.isComputed) {
        description += 'Computed by "' + paramValue.computedMethod + '".';
      }

      if (description) {
        if (!parametersRenderOptions.fields[fieldId]) {
          parametersRenderOptions.fields[fieldId] = {};
        }
        parametersRenderOptions.fields[fieldId].description = {
          htmlTitle: "<p>" + kendo.htmlEncode(description) + "</p>",
          position: paramValue.inArray ? "bottomLabel" : "topValue"
        };
      }
    });

    return {
      menu: [
        {
          beforeContent: '<div class="fa fa-save" />',
          htmlLabel: "",
          iconUrl: "",
          id: "submit",
          important: false,
          label: this.$t("AdminCenterSmartStructure.Record parameters"),
          target: "_self",
          type: "itemMenu",
          url: "#action/document.save",
          visibility: "visible"
        }
      ],
      structure: structureFields,
      title: this.structureData.title + " " + this.$t("AdminCenterSmartStructure.parameters"),
      renderOptions: parametersRenderOptions,
      values: formParamValues
    };
  }

  public mounted(): void {
    this.getParameters();
  }

  public onActionClick(event, data, options): void {
    if (options.eventId === "inheritvalue") {
      const smartForm = this.$refs.ssmForm;
      const fieldId = options.options[0];
      const previousValues = smartForm.getValue(fieldId);
      const buttonTarget = options.target.closest(".dcpAttribute__content");

      if (buttonTarget) {
        buttonTarget.classList.add("todelete");
      }

      if (Array.isArray(previousValues)) {
        if (previousValues.length === 0) {
          previousValues.push({ value: "", inherit: true });
          smartForm.setValue(fieldId, [{ value: "", inherit: true }]);
        } else {
          previousValues.forEach(value => {
            value.inherit = true;
          });
        }
      } else {
        previousValues.inherit = true;
        smartForm.setValue(fieldId, previousValues);
      }
    }
  }
  public onSave(/*event, smartElement, requestOptions*/): void {
    const url = `/api/v2/admin/smart-structures/${this.ssName}/update/parameter/`;

    const updatedData = [];
    const fields = this.$refs.ssmForm.getSmartFields();

    fields.forEach(field => {
      let isToDelete = false;
      const values = field.getValue();
      if (Array.isArray(values)) {
        if (values.length > 0 && values[0].inherit === true) {
          isToDelete = true;
        }
      } else {
        if (values && values.inherit === true) {
          isToDelete = true;
        }
      }

      if (isToDelete) {
        updatedData.push({
          fieldId: field.id,
          toDelete: true
        });
      } else {
        if (field.isModified()) {
          updatedData.push({
            fieldId: field.id,
            fieldValue: field.getValue()
          });
        }
      }
    });

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
    if (field.access === 1) {
      fieldData.display = "read";
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
      this.paramParentValues = {};

      Object.keys(paramsValues).forEach(fieldId => {
        const fieldValue = paramsValues[fieldId];
        if (fieldValue.result) {
          this.paramValues[fieldId] = fieldValue;

          if (fieldValue.parentConfigurationParameters !== undefined) {
            if (fieldValue.parentConfigurationParameters !== null) {
              if (fieldValue.configurationParameter === null) {
                this.paramValues[fieldId].inheritedValue = true;
              } else {
                if (fieldValue.parentConfigurationParameters !== fieldValue.configurationParameter) {
                  this.paramValues[fieldId].overrideValue = fieldValue.parentConfigurationParameters;
                }
              }
            }
          }
        }
      });

      this.structureData = response.data.data.properties;
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
