import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);

@Component
export default class I18nManagerController extends Vue {
  private translationLocale: string = "fr";
  private translationGridData: kendo.data.DataSource = new kendo.data.DataSource(
    {
      pageSize: 50,
      schema: {
        data: response => response.data.data.data,
        total: response => response.data.data.requestParameters.total
      },
      serverFiltering: true,
      serverPaging: true,
      transport: {
        read: options => {
          this.$http
            .get("/api/v2/admin/i18n/fr")
            .then(options.success)
            .catch(options.error);
        }
      }
    }
  );

  @Watch("translationLocale")
  public watchTranslationLocale(value) {
    this.translationGridData = new kendo.data.DataSource({
      pageSize: 50,
      schema: {
        data: response => response.data.data.data,
        total: response => response.data.data.requestParameters.total
      },
      serverFiltering: true,
      serverPaging: true,
      transport: {
        read: options => {
          this.$http
            .get("/api/v2/admin/i18n/" + value)
            .then(options.success)
            .catch(options.error);
        }
      }
    });
    $(this.$refs.i18nGrid)
      .data("kendoGrid")
      .setDataSource(this.translationGridData);
  }

  public mounted() {
    window.addEventListener("offline", e => {
      kendo.ui.progress($("body"), true);
      this.$emit("i18nOffline", e.type);
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
                <input type='text' placeholder="edit translation" class='form-control overriden-translation-input filter-locale' aria-label='Small'>
                <div class="input-group-append">
                    <button class='confirm-override-translation btn btn-outline-secondary'><i class='fa fa-check'></i></button>
                    <button class='cancel-override-translation btn btn-outline-secondary'><i class='fa fa-times'></i></button>
                </div>
            </div>`,
          title: "Overriden translation"
        }
      ],
      dataBound: e => {
        $(".overriden-translation-input").on("change", () => {
          console.log("overriden");
        });

        $(".confirm-override-translation").kendoButton({
          click: confirmEvent => {
            const newVal = $(
              confirmEvent.event.target.closest("tr[role=row]")
            ).find("input")[0].value;
            this.$http.put(`/api/v2/admin/i18n/`, newVal).then(response => {
              if (response.status === 200) {
                this.$emit("EditTranslationSuccess");
              } else {
                this.$emit("EditTranslationFail");
              }
            });
          }
        });

        $(".cancel-override-translation").kendoButton({
          click: cancelEvent => {
            const rowId = cancelEvent.event.target
              .closest("tr[role=row]")
              .getAttribute("data-uid");
            // sets input valueback to server value
            $(cancelEvent.event.target.closest("tr[role=row]")).find(
              "input"
            )[0].value = $(this.$refs.i18nGrid)
              .data("kendoGrid")
              .dataItem(rowId);
          }
        });
      },
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
      sortable: true
    });
    $(this.$refs.i18nGrid)
      .data("kendoGrid")
      .setDataSource(this.translationGridData);
  }

  public changeLocale(e) {
    if (e.id === "i18n-locale-button-fr") {
      this.translationLocale = "fr";
    } else if (e.id === "i18n-locale-button-en") {
      this.translationLocale = "en";
    } else {
      this.$emit(
        "changeLocaleWrongArgument",
        "Wrong locale argument : " + e.id + "id is unknown"
      );
    }
  }

  public importLocaleFile() {
    const importBtn = $(".import-locale-file");
    importBtn.trigger("click");
    importBtn.on("change", e => {
      console.log(e.target);
    });
  }

  public exportLocaleFile() {
    const locale = this.translationLocale === "fr" ? "FR_fr" : "EN_us";
    const date = this.getDate();
    // const fileName = `${locale}-${date}`;
    const fileName = `${locale}`;
    // window.open(`/api/v2/admin/i18n/export/${this.translationLocale}/${fileName}.po`);
  }

  private getDate() {
    const today = new Date();
    const DD = String(today.getDate()).padStart(2, "0");
    const MM = String(today.getMonth() + 1).padStart(2, "0");
    const YYYY = today.getFullYear();
    const HH = String(today.getHours()).padStart(2, "0");
    const mm = String(today.getMinutes()).padStart(2, "0");
    const ss = String(today.getSeconds()).padStart(2, "0");

    return `${YYYY}-${MM}-${DD}-${HH}:${mm}:${ss}`;
  }
}
