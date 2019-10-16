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
            editDisplay: "bool"
          },
          enum_array_key: {
            template:
              '<div class="enum-key-wrapper" mode="view"><div class="enum-key-view-wrapper">{{{attributes.enum_array_key.htmlContent}}}</div><div class="enum-key-edit-wrapper"><input type="text" class="k-textbox enum-key-edit-input"/></div></div>'
          },
          enum_array_translation: {
            template: '<a href="#">Translate</a>'
          },
          enum_array_validate: {
            template:
              '<div class="enum-validate-wrapper" mode="view"><button class="fa fa-check enum-validate-apply-button"><!-- <button class="fa fa-times enum-validate-cancel-button"></div> !>'
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
                  type: "enum",
                  "enumItems": [
                    {
                      "key": "disable",
                      "label": "Disable"
                    },
                    {
                      "key": "enable",
                      "label": "Enable"
                    }
                  ]
                },
                {
                  label: "Actions",
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
        enum_array_label: this.labelArray,
        enum_array_active: this.activeArray
      }
    };
  }

  public selectedEnum: string = "";
  public kendoGrid: any = null;
  // Store temporarily data from a specific line update/add
  public tempModifications: any = {};
  // Store data to send to the server
  public modifications: any = {};
  // SmartForm's filling data
  public keysArray: any = [];
  public labelArray: any = [];
  public activeArray: any = [];
  // Initial enum entries data
  public smartFormModel: any = {};

  private smartFormInitCounter: number = 0;
  private newRowInitCounter: number = 0;

  // Get entries from an Enum
  public loadEnumerate(e) {
    this.keysArray = [];
    this.labelArray = [];
    this.activeArray = [];
    this.selectedEnum = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).enumerate;
    const that = this;
    this.$http.get(`/api/v2/admin/enumdata/${this.selectedEnum}`).then(response => {
      const enumData = response.data.data;
      enumData.forEach((value, index) => {
        that.smartFormModel[index] = _.defaults(value, { key: "", label: "", active: "" });
        that.smartFormInitCounter++;
      });
      that.smartFormModel.size = that.smartFormInitCounter;
      that.buildInitialFormData();
    });
  }

  // Manage how SmartForm's lines are added and act accordingly
  public addEntry(event, smartElement, smartField, type, index) {
    if (smartField.id === "enum_array") {
      if (type === "addLine") {
        // If user's clicking on the "+" button
        if (this.smartFormInitCounter <= 0) {
          this.tempModifications[index] = this.saveTemporaryData("add", index, "", "", "");
          this.setRowMode(true, index);
          this.manageNewRowData(index);
        }
        // If lines are added by the SmartForm's initial build
        else {
          this.smartFormInitCounter--;
        }
      }
    }
  }

  public updateModifications(event, smartElement, smartField, values, index) {
    const currentValues = values.current;
    const smartFormModelLength = Object.keys(this.smartFormModel).length - 1;
    // If it's an update
    if (currentValues.length === smartFormModelLength) {
      // Check what field has been updated and if it's the first time or not
      switch (smartField.id) {
        case "enum_array_label": {
          let activeValue = this.modifications[index]
            ? this.modifications[index].active
            : this.smartFormModel[index].active

          this.modifications[index] = this.saveTemporaryData("update", index, this.smartFormModel[index].key, currentValues[index].value, activeValue);
          break;
        }
        case "enum_array_active": {
          let labelValue = this.modifications[index]
            ? this.modifications[index].label
            : this.smartFormModel[index].label

          this.modifications[index] = this.saveTemporaryData("update", index, this.smartFormModel[index].key, labelValue, currentValues[index].value);
          break;
        }
        default:
          break;
      }
    } else if (currentValues.length > smartFormModelLength) {
      // If updating a newly added row
      switch (smartField.id) {
        case "enum_array_label": {
          if (this.modifications[index]) {
            this.modifications[index].label = currentValues[index].value;
          }
          else {
            this.tempModifications[index].label = currentValues[index].value;
          }
          break;
        }
        case "enum_array_active": {
          if (this.modifications[index]) {
            this.modifications[index].active = currentValues[index].value;
          }
          else {
            this.tempModifications[index].active = currentValues[index].value;
          }
          break;
        }
        default:
          break;
      }
    } else {
      // If deletting a newly added row
      delete this.tempModifications[index];

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
        data: this.modifications,
        enumName: this.selectedEnum
      };
      this.$http.post(`/api/v2/admin/enumupdate/${this.selectedEnum}`, data).then(() => {
        this.modifications = [];
        // @ts-ignore
        this.kendoGrid.dataSource.read();
      });
    }
  }
  public mounted() {
    this.kendoGrid = $(this.$refs.gridWrapper)
      .kendoGrid({
        toolbar: kendo.template('<input type="button" id="clearFilterButton" class="k-button" value="Clear Filter" />'),
        columns: [
          {
            field: "enumerate",
            title: "Enumerate",
          },
          {
            field: "label",
            title: "Label",
          },
          {
            field: "structures",
            title: "Found in structure...",
          },
          {
            field: "fields",
            title: "Fields"
          },
          {
            field: "modifiable",
            title: "Modifiable",
            filterable: false
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
          serverSorting: true,
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
        scrollable: true,
        sortable: true,
        filterable: {
          extra: false,
          operators: {
            string: {
              contains: "Contains"
            }
          },
        },
      })
      .data("kendoGrid");
    let that = this;
    $("#clearFilterButton").click(function () {
      // @ts-ignore
      that.kendoGrid.dataSource.filter({});
    });
  }
  private getRow(rowIndex) {
    return $(`tr[data-line=${rowIndex}]`)[0];
  }

  private manageNewRowData(index) {
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
    // Validate new row data
    $(this.getRow(index))
      .find(".enum-validate-apply-button")
      .on("click", e => {
        this.validateNewRowData(index);
      });
    /* // ToDo : Cancel add row
    $(this.getRow(index))
      .find(".enum-validate-cancel-button")
      .on("click", e => {
        // @ts-ignore
        this.getRow(index).remove()
        delete this.tempModifications[index];
      }); */
  }

  private validateNewRowData(index) {
    if (this.tempModifications[index]) {
      let isKeyOk = true;

      if (this.tempModifications[index].key.length > 0 && this.tempModifications[index].label !== null) {
        for (let [key, value] of Object.entries(this.smartFormModel)) {
          // @ts-ignore
          if (this.tempModifications[index].key === value.key) {
            isKeyOk = !isKeyOk;
          }
        }
      } else {
        isKeyOk = !isKeyOk;
      }
      if (isKeyOk) {
        this.smartFormModel[index] = this.tempModifications[index];
        this.modifications[index] = this.tempModifications[index];
        delete this.tempModifications[index];
        this.insertFormData(index);
      }
      else {
        throw Error("Key value and/or label value are not initialized")
      }
    } else {
      throw Error("temporary modification is not defined.");
    }
  }

  private saveTemporaryData(
    type,
    row,
    key,
    label,
    active,
    from = -1,
    to = -1
  ) {
    return {
      active,
      from,
      key,
      label,
      row,
      to,
      type
    };
  }

  // Fill SmartForm's initial data arrays
  private buildInitialFormData() {
    for (let i = 0; i < this.smartFormModel.size; i++) {
      this.keysArray.push(this.smartFormModel[i].key);
      this.labelArray.push(this.smartFormModel[i].label);
      this.activeArray.push(this.smartFormModel[i].active)
    }
  }

  // Add data in SmartForm's data arrays and complete the "add" process
  private insertFormData(index) {
    this.keysArray[index] = this.smartFormModel[index].key;
    this.labelArray[index] = this.smartFormModel[index].label;
    this.activeArray[index] = this.smartFormModel[index].active;
    this.setFieldValue("enum_array_key", {
      value: this.keysArray[index],
      displayValue: this.keysArray[index],
      index
    });
    this.setRowMode(false, index);
  }

  private setFieldValue(smartFieldId, newValue) {
    //@ts-ignore
    this.$refs.smartForm.setValue(smartFieldId, newValue);
  }
}
