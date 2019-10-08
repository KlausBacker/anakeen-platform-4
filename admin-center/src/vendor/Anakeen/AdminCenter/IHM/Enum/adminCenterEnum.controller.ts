import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.switch";
import * as _ from "underscore";
import Vue from "vue";
import { Component } from "vue-property-decorator";

@Component({
  components: {
    "ank-smart-form": AnkSmartForm,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterEnumController extends Vue {
  get smartFormData() {
    return {
      menu: [
        {
          beforeContent: '<div class="fa fa-save" />',
          iconUrl: "",
          id: "submit",
          important: false,
          label: "Sauver les modifications",
          target: "_self",
          type: "itemMenu",
          url: "#action/enum.save",
          visibility: "visible"
        }
      ],
      renderOptions: {
        fields: {
          enum_array: {
            rowDelDisable: true
          },
          enum_array_active: {
            template: '<input class="enum-form-active-wrapper"/>'
          },
          enum_array_key: {
            template:
              '<div class="enum-key-wrapper" mode="view"><div class="enum-key-view-wrapper">{{{attributes.enum_array_key.htmlContent}}}</div><div class="enum-key-edit-wrapper"><input type="text" class="k-textbox enum-key-edit-input"></div></div>'
          },
          enum_array_translation: {
            template: '<a href="#">Translate</a>'
          },
          enum_array_validate: {
            template:
              '<div class="enum-validate-wrapper" mode="view"><button class="fa fa-check enum-validate-apply-button"><button class="fa fa-times enum-validate-cancel-button"></div>'
          }
        }
      },
      structure: [
        {
          content: [
            {
              content: [
                {
                  display: "read",
                  label: "Key",
                  name: "enum_array_key",
                  type: "text"
                },
                {
                  label: "Label",
                  name: "enum_array_label",
                  type: "text"
                },
                {
                  label: "Translation",
                  name: "enum_array_translation",
                  type: "text"
                },
                {
                  label: "Active",
                  name: "enum_array_active",
                  type: "text"
                },
                {
                  label: " ",
                  name: "enum_array_validate"
                }
              ],
              label: "Entries",
              name: "enum_array",
              type: "array"
            }
          ],
          label: "Enumerate " + this.selectedEnum,
          name: "enum_frame",
          type: "frame"
        }
      ],
      title: "Enumerate management",
      type: "",
      values: {
        enum_array_key: this.keysArray,
        enum_array_label: this.labelArray
      }
    };
  }

  public selectedEnum: string = "";
  public kendoGrid: any = null;
  public smartFormModel: any = {};
  public modifications: any = {};
  public tempModifications: any = {};
  public keysArray: any = [];
  public labelArray: any = [];
  private initCounter: number = 0;

  public loadEnumerate(e) {
    this.keysArray = [];
    this.labelArray = [];
    this.selectedEnum = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).enumerate;
    const that = this;
    this.$http.get(`/api/v2/admin/enumdata/${this.selectedEnum}`).then(response => {
      const enumData = response.data.data;
      enumData.forEach((value, index) => {
        that.smartFormModel[index] = _.defaults(value, { key: "", label: "", enabled: true });
        that.initCounter++;
      });
      that.smartFormModel.size = that.initCounter;
      that.buildInitialFormData();
    });
  }

  public updateModifications(event, smartElement, smartField, values, index) {
    const currentValues = values.current;
    if (currentValues.length === values.previous.length) {
      const labelValue = currentValues[index].value;
      if (this.tempModifications[index]) {
        this.tempModifications[index].label = labelValue;
      } else {
        this.smartFormModel[index].label = labelValue;
        if (this.modifications[index]) {
          this.modifications[index].label = labelValue;
        } else {
          this.modifications[index] = this.getModification("update", index, this.smartFormModel[index].key, labelValue);
        }
      }
    }
  }

  public addEntry(event, smartElement, smartField, type, index) {
    if (smartField.id === "enum_array") {
      if (type === "addLine") {
        if (this.initCounter <= 0) {
          this.tempModifications[index] = this.getModification("add", index, "", "", true);
          this.setRowMode(true, index);
          this.configureRow(index);
        } else {
          this.initCounter--;
          this.initRow(index);
        }
      }
    }
  }

  public setRowMode(edit, rowIndex) {
    const mode = edit ? "edit" : "view";
    const row = this.getRow(rowIndex);
    $(row)
      .find(".enum-key-wrapper")
      .attr("mode", mode);
    $(row)
      .find(".enum-validate-wrapper")
      .attr("mode", mode);

    if (edit) {
    } else {
    }
  }

  public saveModifications(event, smartElement, params) {
    if (params.eventId === "enum.save") {
      const data = {
        data: this.modifications
      };
      this.$http.post(`/api/v2/admin/enumupdate/${this.selectedEnum}`, data).then(() => {});
    }
  }
  public mounted() {
    this.kendoGrid = $(this.$refs.gridWrapper)
      .kendoGrid({
        columns: [
          {
            field: "enumerate",
            title: "Enumerate"
          },
          {
            field: "label",
            title: "Label"
          },
          {
            field: "structures",
            title: "Found in structure..."
          },
          {
            field: "fields",
            title: "Fields"
          },
          {
            field: "modifiable",
            title: "Modifiable"
          },
          {
            command: {
              click: this.loadEnumerate,
              text: "Modify"
            },
            title: "Actions"
          }
        ],
        dataSource: {
          schema: {
            data: "data.data",
            model: {
              fields: {
                enumerate: { type: "string" },
                fields: { type: "string" },
                label: { type: "string" },
                structures: { type: "string" }
              }
            },
            total: "data.total"
          },
          serverFiltering: true,
          serverPaging: true,
          transport: {
            read: {
              dataType: "json",
              url: "/api/v2/admin/enum"
            }
          }
        },
        pageable: {
          pageSize: 20,
          pageSizes: [10, 20, 50]
        },
        scrollable: true
      })
      .data("kendoGrid");
  }
  private getRow(rowIndex) {
    return $(`tr[data-line=${rowIndex}]`)[0];
  }

  private configureRow(index) {
    this.initRow(index);
    $(this.getRow(index))
      .find(".enum-key-edit-input")
      .on("change", e => {
        if (this.tempModifications[index]) {
          // @ts-ignore
          this.tempModifications[index].key = e.target.value;
        } else {
          throw Error("temporary modification is not defined.");
        }
      });
      $(this.getRow(index))
        .find(".enum-validate-apply-button")
        .on("click", e => {
          if (this.tempModifications[index]) {
            this.smartFormModel[index] = this.tempModifications[index];
            this.modifications[index] = this.tempModifications[index];
            delete this.tempModifications[index];
            this.insertFormData(index);
          } else {
            throw Error("temporary modification is not defined.");
          }
        });
        $(this.getRow(index))
          .find(".enum-validate-cancel-button")
          .on("click", e => {
            console.log("Add Canceled")
          });
  }

  private initRow(index) {
    let that = this;
    $($(this.getRow(index)).find(".enum-form-active-wrapper")).kendoSwitch({
      change(e) {
        const enabledValue = e.checked;
        if (that.tempModifications[index]) {
          // If temporary row (Added and not validated yet)
          that.tempModifications[index].enabled = enabledValue;
        } else {
          that.smartFormModel[index].enabled = enabledValue;
          if (that.modifications[index]) {
            // Already modified row
            that.modifications[index].enabled = enabledValue;
          } else {
            // Newly modified row
            that.modifications[index] = that.getModification(
              "update",
              index,
              that.smartFormModel[index].key,
              that.smartFormModel[index].label,
              enabledValue
            );
          }
        }
      },
      messages: {
        checked: "active",
        unchecked: "disabled"
      }
    });
  }

  private getModification(
    type,
    row,
    key = this.smartFormModel[row].key,
    label = this.smartFormModel[row].label,
    enabled = this.smartFormModel[row].enabled,
    from = -1,
    to = -1
  ) {
    return {
      enabled,
      from,
      key,
      label,
      row,
      to,
      type
    };
  }

  private buildInitialFormData() {
    for (let i = 0; i < this.smartFormModel.size; i++) {
      this.keysArray.push(this.smartFormModel[i].key);
      this.labelArray.push(this.smartFormModel[i].label);
    }
  }

  private insertFormData(index) {
    this.keysArray[index] = this.smartFormModel[index].key;
    this.labelArray[index] = this.smartFormModel[index].label;
    this.setRowMode(false, index);
  }
}
