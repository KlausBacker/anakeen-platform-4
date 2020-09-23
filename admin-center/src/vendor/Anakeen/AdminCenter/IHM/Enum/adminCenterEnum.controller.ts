/* eslint-disable */
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm.esm";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.switch";
import * as _ from "underscore";
import { Component, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import * as $ from "jquery";

@Component({
  components: {
    "ank-smart-form": () => {
      return AnkSmartForm;
    },
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterEnumController extends Mixins(AnkI18NMixin) {
  public $refs!: {
    [key: string]: any;
  };
  // Useful for translate's filters
  public selectedEnum: string = "";
  public actualKey: string = "";
  public acualLabel: string = "";
  public language: string = "";

  public isEnumEditable: boolean = false;
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
    const display = this.isEnumEditable ? "write" : "read";
    const visibility = this.isEnumEditable ? "visible" : "hidden";
    return {
      menu: [
        {
          beforeContent: '<div class="fa fa-save" />',
          iconUrl: "",
          id: "submit",
          important: false,
          label: this.$t("AdminCenterEnum.Sauver les modifications"),
          target: "_self",
          type: "itemMenu",
          url: "#action/enum.save",
          visibility: visibility
        }
      ],
      renderOptions: {
        fields: {
          enum_array_active: {
            editDisplay: "bool"
          },
          enum_array_translation: {
            template: `<a data-role="adminRouterLink" class="translate-button" href="#">${this.$t(
              "AdminCenterEnum.btn Translate"
            )}</a>`
          }
        }
      },
      structure: [
        {
          label: this.$t("AdminCenterEnum.Enumerate") + " " + this.selectedEnum,
          name: "enum_frame",
          type: "frame",
          content: [
            {
              label: this.$t("AdminCenterEnum.Entries"),
              name: "enum_array",
              type: "array",
              display: display,
              content: [
                {
                  label: this.$t("AdminCenterEnum.Key"),
                  name: "enum_array_key",
                  type: "text"
                },
                {
                  label: this.$t("AdminCenterEnum.Label"),
                  name: "enum_array_label",
                  type: "text"
                },
                {
                  label: this.$t("AdminCenterEnum.Translation"),
                  name: "enum_array_translation",
                  type: "text"
                },
                {
                  label: this.$t("AdminCenterEnum.Active"),
                  name: "enum_array_active",
                  type: "enum",
                  enumItems: [
                    {
                      key: "disable",
                      label: this.$t("AdminCenterEnum.Disable")
                    },
                    {
                      key: "enable",
                      label: this.$t("AdminCenterEnum.Enable")
                    }
                  ]
                }
              ]
            }
          ]
        }
      ],
      title: this.$t("AdminCenterEnum.Enumerate") + " " + this.selectedEnum,
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
    this.isEnumEditable = this.kendoGrid.dataItem($(e.currentTarget).closest("tr")).modifiable;
    const that = this;
    kendo.ui.progress($(".enum-form-wrapper", this.$el), true);
    this.$http
      .get(`/api/v2/admin/enumdata/${this.selectedEnum}`)
      .then(response => {
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
      })
      .catch(() => {
        kendo.ui.progress($(".enum-form-wrapper", this.$el), false);
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
        let key;
        if (this.isEnumEditable === true) {
          key = translateButtons[i]
            .closest("[data-line]")
            .querySelector("[name=enum_array_key]")
            .getAttribute("value");
        } else {
          key = translateButtons[i].closest("[data-line]").querySelector(".dcpAttribute__content__value").textContent;
        }

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

    // @ts-ignore
    this.kendoGrid = $(this.$refs.gridWrapper)
      .kendoGrid({
        columns: [
          {
            field: "enumerate",
            title: this.$t("AdminCenterEnum.Enumerate")
          },
          {
            field: "label",
            title: this.$t("AdminCenterEnum.Label")
          },
          {
            field: "structures",
            title: this.$t("AdminCenterEnum.Structure")
          },
          {
            field: "fields",
            title: this.$t("AdminCenterEnum.Fields")
          },
          {
            field: "action",
            title: "Action",
            template: data => {
              if (data.modifiable === true) {
                return (
                  '<a role="button" class="k-button k-button-icontext action-button" href="#">' +
                  this.$t("AdminCenterEnum.Modify") +
                  "</a>"
                );
              }
              return (
                '<a role="button" class="k-button k-button-icontext action-button" href="#">' +
                this.$t("AdminCenterEnum.Consult") +
                "</a>"
              );
            }
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
          pageSizes: [50, 100, 200],
          messages: {
            itemsPerPage: this.$t("AdminCenterKendoGridTranslation.items per page"),
            display: this.$t("AdminCenterKendoGridTranslation.{0}-{1}of{2}items"),
            refresh: this.$t("AdminCenterKendoGridTranslation.Refresh"),
            NoData: this.$t("AdminCenterKendoGridTranslation.No data")
          }
        },
        scrollable: true,
        sortable: true,
        filterable: {
          extra: false,
          operators: {
            string: {
              contains: this.$t("AdminCenterKendoGridTranslation.contains")
            }
          },
          messages: {
            info: this.$t("AdminCenterKendoGridTranslation.Filter by") + ": ",
            operator: this.$t("AdminCenterKendoGridTranslation.Choose operator"),
            clear: this.$t("AdminCenterKendoGridTranslation.Clear"),
            filter: this.$t("AdminCenterKendoGridTranslation.Apply"),
            value: this.$t("AdminCenterKendoGridTranslation.Choose value"),
            additionalValue: this.$t("AdminCenterKendoGridTranslation.Aditional value"),
            title: this.$t("AdminCenterKendoGridTranslation.Aditional filter by")
          }
        },
        filterMenuInit(e) {
          $(e.container)
            .find(".k-primary")
            .on("click", event => {
              const val = $(e.container)
                .find('[title="Value"]')
                .val();
              if (val == "") {
                // @ts-ignore
                that.kendoGrid.dataSource.filter({});
              }
            });
        },
        dataBound: e => {
          $(".action-button").on("click", e => {
            this.loadEnumerate(e);
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
    const enumArrayKeyInputs = document.querySelectorAll("[name='enum_array_key']");
    const delButtonsList = $(".dcpArray__content__tollCell__delete");
    const selectButtonsList = $(".dcpArray__content__toolCell__check");
    // Remove the "duplicate selected line" button
    if (this.isEnumEditable === true) {
      $(".dcpArray__button--copy")[0].remove();
    }
    // Remove the "delete line" button
    // @ts-ignore
    delButtonsList.each((key, val) => {
      val.remove();
    });
    // Remove the "select line" button
    // @ts-ignore
    selectButtonsList.each((key, val) => {
      val.remove();
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
