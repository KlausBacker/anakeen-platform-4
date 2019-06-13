import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import Vue from "vue";
Vue.use(ButtonsInstaller);
import { id } from "postcss-selector-parser";
import { Component } from "vue-property-decorator";

@Component
export default class I18nManagerController extends Vue {
  private readonly FILTER_CLASS_LIST = new Array(
    "filter-locale-type",
    "filter-locale-id",
    "filter-locale-context",
    "filter-locale-overridentranslation",
    "filter-locale-servertranslation"
  );
  private translationLocale: string = "fr";
  public mounted() {
    $(this.$refs.i18nGrid).kendoGrid({
      columns: [
        {
          field: "gridId",
          hidden: true,
          title: "gridId"
        },
        {
          field: "section",
          headerTemplate: `<div class="filter-locale-header">
                            <span>Type</span><BR>
                            <input type="text" class="filter-locale filter-locale-type" aria-label="Small"/></div>`,
          title: "Type"
        },
        {
          field: "msgctxt",
          headerTemplate: `<div class="filter-locale-header"><span>Contexte</span><BR><input type="text"class="filter-locale filter-locale-context" aria-label="Small"/></div>`,
          title: "Contexte"
        },
        {
          field: "msgid",
          headerTemplate: `<div class="filter-locale-header"><span>ID</span><BR><input type="text" class="filter-locale filter-locale-id" aria-label="Small"/></div>`,
          title: "ID"
        },
        {
          field: "msgstr",
          headerTemplate: `<div class="filter-locale-header"><span>Server translation</span><BR><input type="text"class="filter-locale filter-locale-servertranslation" aria-label="Small"/></div>`
        },
        {
          field: "overridentranslation",
          headerTemplate: `<div class="filter-locale-header"><span>Overriden translation</span><BR><input type="text"class="filter-locale filter-locale-overridentranslation" aria-label="Small"/></div>`,
          template: `<div class="input-group">
                <input type='text' class='overriden-translation-input filter-locale' aria-label='Small'>
                <div class="input-group-append">
                    <button class='confirm-override-translation btn btn-primary'><i class='fa fa-check'></i></button>
                    <button class='cancel-override-translation btn btn-primary'><i class='fa fa-times'></i></button>
                </div>
            </div>`
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
          data: "data",
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
          }
        },
        transport: {
          read: {
            url: () => {
              return `/api/v2/admin/i18n/${this.translationLocale}`;
            }
          }
        }
      }),
      pageable: {
        alwaysVisible: true,
        buttonCount: 5,
        pageSizes: [50, 100, 200],
        refresh: true
      },
      sortable: true
    });
    $(this.$refs.i18nGrid).on("keypress", ".filter-locale", e => {
      if (e.key === "Enter") {
        this.filter(e.target);
      }
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
  public filter(filterObject) {
    const filterClassResult = this.FILTER_CLASS_LIST.filter(value =>
      filterObject.classList.contains(value)
    );
    if (filterClassResult.length) {
      const filterClass = filterClassResult[0].replace("filter-locale-", "");
      $(this.$refs.i18nGrid)
        .data("kendoGrid")
        .dataSource.filter({
          field: filterClass,
          operator: "contains",
          value: filterObject.value
        });
    }
  }
}
