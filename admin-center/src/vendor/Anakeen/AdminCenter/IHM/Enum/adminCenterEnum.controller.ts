import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.switch";
import * as _ from "underscore";
import { Component, Vue } from "vue-property-decorator";

@Component({
  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    },
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterEnumController extends Vue {
  // Useful for translate's filters
  public selectedEnum: string = "";
  public actualKey: string = "";
  public acualLabel: string = "";
  public language: string = "";

  public kendoGrid: any = null;
  // Store data to send to the server
  public modifications: any = {};
  // Initial enum entries data
  public smartFormModel: any = {};
  // SmartForm's filling data
  public keysArray: any = [];
  public labelArray: any = [];
  public activeArray: any = [];

  private smartFormDataCounter: number = 0;

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
          enum_array_active: {
            editDisplay: "bool"
          },
          enum_array_translation: {
            template: `<a data-role="adminRouterLink" class="translate-button" href="#">Translate</a>`
          }
        }
      },
      structure: [
        {
          label: "Enumerate " + this.selectedEnum,
          name: "enum_frame",
          type: "frame",
          content: [
            {
              label: "Entries",
              name: "enum_array",
              type: "array",
              content: [
                {
                  display: "write",
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
                  enumItems: [
                    {
                      key: "disable",
                      label: "Disable"
                    },
                    {
                      key: "enable",
                      label: "Enable"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ],
      title: "Enumerate " + this.selectedEnum,
      type: "",
      values: {
        enum_array_key: this.keysArray,
        enum_array_label: this.labelArray,
        enum_array_active: this.activeArray
      }
    };
  }
  // Get entries from an Enum
  public loadEnumerate(e) {
    this.keysArray = [];
    this.labelArray = [];
    this.activeArray = [];
    this.selectedEnum = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).enumerate;
    const that = this;
    kendo.ui.progress($(".enum-form-wrapper", this.$el), true);
    this.$http.get(`/api/v2/admin/enumdata/${this.selectedEnum}`).then(response => {
      kendo.ui.progress($(".enum-form-wrapper", this.$el), false);
      const enumData = response.data.data;
      this.modifications = {};
      enumData.forEach((value, index) => {
        that.smartFormModel[index] = _.defaults(value, { key: "", label: "", active: "", eorder: "" });
        that.modifications[index] = that.smartFormModel[index];
        that.smartFormDataCounter++;
      });
      that.smartFormModel.size = that.smartFormDataCounter;
      that.buildInitialFormData();
    });
  }

  // Manage how SmartForm's lines are added and act accordingly
  public manageRows(event, smartElement, smartField, type, index) {
    if (smartField.id === "enum_array") {
      switch (type) {
        case "addLine": {
          // If user's clicking on the "+" button
          if (this.smartFormDataCounter <= 0) {
            // @ts-ignore
            this.modifications[index] = { key: "", label: "", active: "enable", eorder: index + 1 };
          }
          // If lines are added by the SmartForm's initial build
          else {
            this.smartFormDataCounter--;
          }
          break;
        }
        case "removeLine": {
          delete this.modifications[index];
          break;
        }
        case "moveLine": {
          // '+1' because array's index start at 0 but eorder column in db starts at 1
          const fromLine = index.fromLine + 1;
          const toLine = index.toLine + 1;
          this.changeEnumOrder(fromLine, toLine);
          break;
        }
      }
    }
  }

  public smartFormReady(event, smartElement) {
    if (this.getRow(0) !== undefined) {
      // Manage actions for already existing rows
      this.disableInitialDataRowAction();
      // Add event listeners on "Translate" buttons
      const translateButtons = document.getElementsByClassName("translate-button");
      for (let i = 0; i < translateButtons.length; i++) {
        const key = translateButtons[i]
          .closest("[data-line]")
          .querySelector("[name=enum_array_key]")
          .getAttribute("value");
        const selectedEnum = this.selectedEnum;
        const language = this.language;
        // @ts-ignore
        translateButtons[i].setAttribute(
          "href",
          `/admin/i18n/${language}?section=Enum&msgctxt=${selectedEnum}&msgid=${key}`
        );
      }
    }
  }

  public updateModifications(event, smartElement, smartField, values, index) {
    if (values.current[index] !== undefined) {
      // @ts-ignore
      const entryToUpdate = Object.values(this.modifications).find(entry => entry.eorder == index + 1);
      switch (smartField.id) {
        case "enum_array_key": {
          // @ts-ignore
          entryToUpdate.key = values.current[index].value;
          break;
        }
        case "enum_array_label": {
          // @ts-ignore
          entryToUpdate.label = values.current[index].value;
          break;
        }
        case "enum_array_active": {
          // @ts-ignore
          entryToUpdate.active = values.current[index].value;
          break;
        }
        default:
          break;
      }
    }
  }

  public saveModifications(event, smartElement, params) {
    if (params.eventId === "enum.save") {
      let valid = true;
      Object.values(this.modifications).forEach(modif => {
        Object.values(modif).forEach(element => {
          if (element === null) {
            valid = false;
            return;
          }
        });
      });
      if (valid) {
        const data = {
          data: this.modifications
        };
        this.$http.post(`/api/v2/admin/enumupdate/${this.selectedEnum}`, data).then(() => {
          // @ts-ignore
          this.kendoGrid.dataSource.read();
        });
      } else {
        $(this.$refs.smartFormAlert).kendoDialog({
          title: false,
          content: "<center><h4>Please fill all fields</h4></center>",
          size: "small",
          actions: [
            {
              text: "OK"
            }
          ],
          closable: false,
          animation: {
            open: {
              effects: "fade:in",
              duration: 150
            }
          },
          visible: false
        });
        $(this.$refs.smartFormAlert)
          .data("kendoDialog")
          .open();
      }
    }
  }
  public mounted() {
    const that = this;
    this.$http
      .get(`/api/v2/ui/users/current`)
      .then(response => (this.language = response.data.locale === "fr_FR.UTF-8" ? "fr" : "en"));

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
            title: "Structure"
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
          pageSize: 50,
          pageSizes: [50, 100, 200]
        },
        scrollable: true,
        sortable: true,
        filterable: {
          extra: false,
          operators: {
            string: {
              contains: "Contains"
            }
          }
        },
        filterMenuInit(e) {
          $(e.container)
            .find(".k-primary")
            .click(function(event) {
              const val = $(e.container)
                .find('[title="Value"]')
                .val();
              if (val == "") {
                // @ts-ignore
                that.kendoGrid.dataSource.filter({});
              }
            });
        }
      })
      .data("kendoGrid");
  }

  private getRow(rowIndex) {
    return $(`tr[data-line=${rowIndex}]`)[0];
  }

  // Fill SmartForm's initial data arrays
  private buildInitialFormData() {
    for (let i = 0; i < this.smartFormModel.size; i++) {
      this.keysArray.push(this.smartFormModel[i].key);
      this.labelArray.push(this.smartFormModel[i].label);
      this.activeArray.push(this.smartFormModel[i].active);
    }
  }

  // Add data in SmartForm's data arrays and complete the "add" process
  private insertFormData(index) {
    this.keysArray[index] = this.smartFormModel[index].key;
    this.labelArray[index] = this.smartFormModel[index].label;
    this.activeArray[index] = this.smartFormModel[index].active;
  }

  private disableInitialDataRowAction() {
    const delButtonsList = document.querySelectorAll("[Title='Delete line']");
    const selectButtonsList = document.querySelectorAll("[Title='Select line']");
    const enumArrayKeyInputs = document.querySelectorAll("[name='enum_array_key']");
    // Remove the "duplicate selected line" button
    document.querySelectorAll("[Title='Dupliquer la ligne sélectionnée']")[0].remove();
    // Remove the "delete line" button
    delButtonsList.forEach(deleteButton => {
      deleteButton.remove();
    });
    // Remove the "select line" button
    selectButtonsList.forEach(selectButton => {
      selectButton.remove();
    });
    // Make "keys" read-only for already existing enums and no deletables
    enumArrayKeyInputs.forEach(enumArrayKeyInput => {
      enumArrayKeyInput.setAttribute("disabled", "");
      enumArrayKeyInput.nextElementSibling.remove();
    });
  }

  private changeEnumOrder(fromLine, toLine) {
    for (const i in this.modifications) {
      if (this.modifications.hasOwnProperty(i)) {
        if (fromLine > toLine) {
          if (this.modifications[i].eorder < fromLine && this.modifications[i].eorder >= toLine) {
            Number(this.modifications[i].eorder++);
          } else if (this.modifications[i].eorder == fromLine) {
            this.modifications[i].eorder = toLine;
          }
        } else if (fromLine < toLine) {
          if (this.modifications[i].eorder > fromLine && this.modifications[i].eorder <= toLine) {
            Number(this.modifications[i].eorder--);
          } else if (this.modifications[i].eorder == fromLine) {
            this.modifications[i].eorder = toLine;
          }
        }
      }
    }
  }
}
