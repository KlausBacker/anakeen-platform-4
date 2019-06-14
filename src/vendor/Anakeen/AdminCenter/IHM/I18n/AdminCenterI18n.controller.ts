import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import Vue from "vue";
Vue.use(ButtonsInstaller);
import { Component } from "vue-property-decorator";

@Component
export default class I18nManagerController extends Vue {

  private translationLocale: string = "fr";
  public mounted() {
    window.addEventListener("offline", e => {
      console.log(e);
      kendo.ui.progress($("body"), true);
      this.$emit(
        "i18nOffline",
        e.type
      );
    });
    window.addEventListener("online", () => {
      kendo.ui.progress($("body"), false);
    });
    $(this.$refs.i18nGrid).kendoGrid({
      columns: [
        {
          field: "gridId",
          hidden: true,
          title: "gridId"
        },
        {
          field: "section",
          filterable: {
            cell: {
              operator: "contains",
              showOperators: false
            }
          },
          minResizableWidth: 25,
          title: "Type"
        },
        {
          field: "msgctxt",
          filterable: {
            cell: {
              operator: "contains",
              showOperators: false
            }
          },
          minResizableWidth: 25,
          title: "Contexte"
        },
        {
          field: "msgid",
          filterable: {
            cell: {
              operator: "contains",
              showOperators: false
            }
          },
          minResizableWidth: 25,
          title: "ID"
        },
        {
          field: "msgstr",
          filterable: {
            cell: {
              operator: "contains",
              showOperators: false
            }
          },
          minResizableWidth: 25,
          title: "Server translation"
        },
        {
          field: "overridentranslation",
          filterable: false,
          minResizableWidth: 25,
          template: `<div class="input-group">
                <input type='text' placeholder="change translation" class='form-control overriden-translation-input filter-locale' aria-label='Small'>
                <div class="input-group-append">
                    <button class='confirm-override-translation btn btn-outline-secondary'><i class='fa fa-check'></i></button>
                    <button class='cancel-override-translation btn btn-outline-secondary'><i class='fa fa-times'></i></button>
                </div>
            </div>`,
          title: "Overriden translation",
        }
      ],
      dataBound: e => {
        $(".overriden-translation-input").on("change", () => {
          console.log("overriden");
        });
        $(".confirm-override-translation").kendoButton({
          click: () => {
            console.log("confirm");
          }
        });
        $(".cancel-override-translation").kendoButton({
          click: cancelEvent => {
            console.log("cancel");
            const rowId = cancelEvent.event.target
              .closest("tr[role=row]")
              .getAttribute("data-uid");
            const oldVal = $(this.$refs.i18nGrid)
              .data("kendoGrid")
              .dataItem(rowId);
            $(cancelEvent.event.target.closest("tr[role=row]")).find(
              "input"
            )[0].value = 123;
          }
        });
      },
      dataSource: new kendo.data.DataSource({
        pageSize: 50,
        schema: {
          data: response => {
            return response.data.data;
          },
          model: {
            fields: {
              gridId: {
                type: "string"
              },
              msgctxt: {
                type: "string"
              },
              msgid: {
                type: "string"
              },
              msgstr: {
                type: "string"
              },
              overridentranslation: {
                type: "string"
              },
              section: {
                type: "string"
              }
            },
            id: "gridId"
          },
          total: response => {
            return response.data.requestParameters.total;
          }
        },
        serverFiltering: true,
        serverPaging: true,
        transport: {
          read: {
            dataType: "json",
            type: "get",
            url: () => {
              return `/api/v2/admin/i18n/${this.translationLocale}`;
            }
          }
        }
      }),
      filterable: {
        extra: false,
        mode: "row"
      },
      pageable: {
        alwaysVisible: true,
        buttonCount: 5,
        pageSizes: [50, 100, 200],
        refresh: true
      },
      resizable: true,
      sortable: true,
    });
  }
  public changeLocale(e) {
    if (e.id === "i18n-locale-button-fr") {
      this.translationLocale = "fr";
      $(this.$refs.i18nGrid)
        .data("kendoGrid")
        .dataSource.read();
    } else if (e.id === "i18n-locale-button-en") {
      this.translationLocale = "en";
      $(this.$refs.i18nGrid)
        .data("kendoGrid")
        .dataSource.read();
    } else {
      this.$emit(
        "changeLocaleWrongArgument",
        "Wrong locale argument : " + e.id + "id is unknown"
      );
    }
  }
  public importLocaleFile() {
    console.log("Import Locale");
  }
  public exportLocaleFile() {
    console.log("Export Locale");
  }
}
