import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import { Component, Prop, Vue, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);
declare var kendo;
// noinspection JSUnusedGlobalSymbols
@Component
export default class I18nManagerController extends Vue {
  private static formatForTextarea(str) {
    if (str === null || str.match(/^\s*$/) !== null) {
      return "";
    } else {
      return str.replace(/\\n/g, "\n");
    }
  }
  @Prop({ type: Array, default: [] })
  public i18nFilters: Array<Object>;
  @Prop({ type: String, default: "" })
  public lang: string;

  @Watch("i18nFilters", { immediate: true, deep: true })
  public watchI18nFilters() {
    this.filters = this.i18nFilters;
    if (this.$refs.i18nGrid) {
      $(this.$refs.i18nGrid)
        .data("kendoGrid")
        .dataSource.read();
    }
  }
  public filters = [];

  private translationLocale: string = "fr";
  private translationFilterableOptions: kendo.data.DataSourceFilter = {
    cell: {
      operator: "contains",
      showOperators: false,
      template: e => {
        e.element.kendoAutoComplete({
          noDataTemplate: "",
          serverFiltering: false,
          valuePrimitive: true
        });
      }
    }
  };
  private translationGridData: kendo.data.DataSource = new kendo.data.DataSource({
    pageSize: 50,
    schema: {
      data: response => response.data.data.data,
      total: response => response.data.data.requestParameters.total
    },
    serverFiltering: true,
    serverPaging: true,
    serverSorting: true,
    transport: {
      read: options => {
        this.$http
          .get(`/api/v2/admin/i18n/fr`, {
            params: options.data,
            paramsSerializer: kendo.jQuery.param
          })
          .then(options.success)
          .catch(options.error);
      }
    }
  });

  @Watch("translationLocale")
  public watchTranslationLocale(value) {
    const tb = $(".i18n-toolbar-locale").data("kendoToolBar");
    tb.toggle("#i18n-locale-button-" + value, true);
    this.translationGridData = new kendo.data.DataSource({
      pageSize: 50,
      schema: {
        data: response => response.data.data.data,
        total: response => response.data.data.requestParameters.total
      },
      serverFiltering: true,
      serverPaging: true,
      serverSorting: true,
      transport: {
        read: options => {
          this.$http
            .get(`/api/v2/admin/i18n/${value}`, {
              params: options.data,
              paramsSerializer: kendo.jQuery.param
            })
            .then(options.success)
            .catch(options.error);
        }
      }
    });
    $(this.$refs.i18nGrid)
      .data("kendoGrid")
      .setDataSource(this.translationGridData);
    setTimeout(() => {
      if (value === "fr") {
        $(".overriden-translation-input").attr("placeholder", "modifier la traduction");
      } else {
        $(".overriden-translation-input").attr("placeholder", "edit translation");
      }
    }, 300);
  }

  public mounted() {
    window.addEventListener("offline", e => {
      this.$emit("i18nOffline", e.type);
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
          filterable: this.translationFilterableOptions,
          minResizableWidth: 25,
          title: "Type"
        },
        {
          field: "msgctxt",
          filterable: this.translationFilterableOptions,
          minResizableWidth: 25,
          title: "Contexte"
        },
        {
          field: "msgid",
          filterable: this.translationFilterableOptions,
          minResizableWidth: 25,
          template: rowData => {
            return this.escapeHtml(rowData.msgid).replace(/\\n/g, "&para;<br/>");
          },
          title: "ID"
        },
        {
          field: "msgstr",
          filterable: this.translationFilterableOptions,
          minResizableWidth: 25,
          template: rowData => {
            let str;
            if (rowData.plurals) {
              str = "";
              // tslint:disable-next-line:prefer-for-of
              for (let i = 0; i < rowData.plurals.length - 1; i++) {
                str += this.escapeHtml(rowData.plurals[i]) + "<hr>";
              }
              str += rowData.plurals[rowData.plurals.length - 1];
            } else {
              str = this.escapeHtml(rowData.msgstr);
            }
            return str.replace(/\\n/g, "&para;<br/>");
          },
          title: "Server translation"
        },
        {
          field: "override",
          filterable: this.translationFilterableOptions,
          minResizableWidth: 25,
          template: rowData => {
            if (rowData.pluralid) {
              if (!rowData.override) {
                rowData.override = ["", ""];
              }

              return `<div class="input-group">
                <textarea rows="1" cols="50" wrap="hard" class='form-control overriden-translation-input-singular filter-locale' aria-label='Small'>${I18nManagerController.formatForTextarea(
                  rowData.override[0]
                )}</textarea>
                <div class="input-group-append">
                    
                </div>
            </div>
            <hr/>
            <div class="input-group">
                <textarea rows="1" cols="50" wrap="hard" class='form-control overriden-translation-input-plural filter-locale' aria-label='Small'>${I18nManagerController.formatForTextarea(
                  rowData.override[1]
                )}</textarea>
                <div class="input-group-append">
                    <button class='confirm-override-translation-plural btn btn-outline-secondary' disabled><i class='fa fa-check'></i></button>
                    <button class='cancel-override-translation-plural btn btn-outline-secondary'><i class='fa fa-times'></i></button>
                </div>
            </div>`;
            } else {
              return `<div class="input-group">
                  <textarea rows="1" cols="50" wrap="hard" class='form-control overriden-translation-input-singular filter-locale'>${I18nManagerController.formatForTextarea(
                    rowData.override
                  )}</textarea>
                  <div class="input-group-append">
                      <button class='confirm-override-translation-singular btn btn-outline-secondary' disabled><i class='fa fa-check'></i></button>
                      <button class='cancel-override-translation-singular btn btn-outline-secondary'><i class='fa fa-times'></i></button>
                  </div>
                </div>`;
            }
          },
          title: "Overriden translation"
        }
      ],
      dataBound: () => {
        this.setEventInput();
        this.setEventSingularConfirm();
        this.setEventPluralConfirm();
        this.setEventSingularCancel();
        this.setEventPluralCancel();
        if (this.filters.length > 0) {
          this.setFilters();
        }
      },
      dataSource: this.translationGridData,
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
  }

  public escapeHtml(s) {
    return $("<div/>")
      .text(s)
      .html();
  }
  public changeLocale(e) {
    if (e.id === "i18n-locale-button-fr") {
      this.translationLocale = "fr";
    } else if (e.id === "i18n-locale-button-en") {
      this.translationLocale = "en";
    } else {
      this.$emit("changeLocaleWrongArgument", "Wrong locale argument : " + e.id + "id is unknown");
    }
  }

  public importLocaleFile() {
    const $importBtn = $(".import-locale-file");
    $importBtn.trigger("click");
    $importBtn.one("change", () => {
      const formData = new FormData();

      kendo.ui.progress($("body"), true);
      formData.append("file", (this.$refs.importFile as HTMLInputElement).files[0]);
      this.$http
        .post(`/api/v2/admin/i18n/${encodeURIComponent(this.translationLocale)}/`, formData, {
          headers: {
            "Content-Type": "multipart/form-data"
          }
        })
        .then(() => {
          $(this.$refs.i18nGrid)
            .data("kendoGrid")
            .dataSource.read();
        })
        .finally(() => {
          $importBtn.val("");
          kendo.ui.progress($("body"), false);
        });
    });
  }

  public exportLocaleFile() {
    window.open(`/api/v2/admin/i18n/${this.translationLocale}/custom.po`, "_self");
  }

  private setFilters() {
    $(this.$refs.i18nGrid)
      .data("kendoGrid")
      .dataSource.filter(this.filters);
    this.filters = [];
  }
  private setEventSingularConfirm() {
    $(".confirm-override-translation-singular").kendoButton({
      click: confirmEvent => {
        kendo.ui.progress($("body"), true);
        const rowData: any = $(this.$refs.i18nGrid)
          .data("kendoGrid")
          .dataItem($(confirmEvent.event.target).closest("tr[role=row]"));
        let newVal;
        const textareaVal = $(confirmEvent.event.target.closest("tr[role=row]")).find("textarea")[0].value;
        if (rowData.pluralid) {
          newVal = {
            msgstr: textareaVal,
            plural: 0,
            pluralid: rowData.pluralid
          };
        } else {
          newVal = {
            msgstr: textareaVal
          };
        }

        newVal.section = rowData.section;
        this.setSingularTranslation(newVal, rowData);
        $(confirmEvent.sender.element[0])
          .data("kendoButton")
          .enable(false);
      }
    });
  }

  private setEventPluralConfirm() {
    $(".confirm-override-translation-plural").kendoButton({
      click: confirmEvent => {
        kendo.ui.progress($("body"), true);
        const rowData: any = $(this.$refs.i18nGrid)
          .data("kendoGrid")
          .dataItem($(confirmEvent.event.target).closest("tr[role=row]"));
        const textarea = $(confirmEvent.event.target.closest("tr[role=row]")).find("textarea");
        const newVal = JSON.stringify({
          msgstr: [textarea[0].value, textarea[1].value],
          plural: 1,
          pluralid: rowData.pluralid,
          plurals: rowData.plurals
        });
        this.setPluralTranslation(newVal, rowData);
        $(confirmEvent.sender.element[0])
          .data("kendoButton")
          .enable(false);
      }
    });
  }

  private setEventSingularCancel() {
    $(".cancel-override-translation-singular").kendoButton({
      click: cancelEvent => {
        const $td = $(cancelEvent.event.target).closest("td");

        $td.find("textarea").val("");
        const rowData: any = $(this.$refs.i18nGrid)
          .data("kendoGrid")
          .dataItem($(cancelEvent.event.target).closest("tr[role=row]"));
        const newVal = {
          msgstr: "",
          plural: 1,
          pluralid: rowData.pluralid
        };
        this.setSingularTranslation(newVal, rowData);
      }
    });
  }

  private setEventPluralCancel() {
    $(".cancel-override-translation-plural").kendoButton({
      click: cancelEvent => {
        kendo.ui.progress($("body"), true);
        const rowData: any = $(this.$refs.i18nGrid)
          .data("kendoGrid")
          .dataItem($(cancelEvent.event.target).closest("tr[role=row]"));
        const newVal = {
          msgstr: "",
          plural: 1,
          pluralid: rowData.pluralid
        };
        this.setPluralTranslation(newVal, rowData);
        $(cancelEvent.sender.element[0].previousElementSibling)
          .data("kendoButton")
          .enable(false);
      }
    });
  }

  private setEventInput() {
    const input = $(".overriden-translation-input-singular, .overriden-translation-input-plural");

    input.on("input", event => {
      const cancelBtn = $(event.target.nextElementSibling.children[1]);
      const confirmBtn = $(event.target.nextElementSibling.children[0]);
      if (confirmBtn.length > 0) {
        confirmBtn.data("kendoButton").enable(true);
      }
      if (confirmBtn.length > 0) {
        cancelBtn.data("kendoButton").enable(true);
      }
    });
  }

  private setSingularTranslation(newVal, rowData) {
    kendo.ui.progress($("body"), true);
    const msgctxtData = rowData.msgctxt !== null ? rowData.msgctxt : "";
    const url = `/api/v2/admin/i18n/${encodeURIComponent(this.translationLocale)}/${encodeURIComponent(
      msgctxtData
    )}/${encodeURIComponent(rowData.msgid)}`;
    const jsonHeader = {
      headers: {
        "Content-type": "application/json"
      }
    };

    this.$http
      .put(url, JSON.stringify(newVal), jsonHeader)
      .then(() => {
        this.$emit("EditTranslationSuccess");
        kendo.ui.progress($("body"), false);
      })
      .catch(() => {
        this.$emit("EditTranslationFail");
        kendo.ui.progress($("body"), false);
      });
  }
  private setPluralTranslation(newVal, rowData) {
    const msgctxtData = rowData.msgctxt !== null ? rowData.msgctxt : "";
    const url = `/api/v2/admin/i18n/${encodeURIComponent(this.translationLocale)}/${encodeURIComponent(
      msgctxtData
    )}/${encodeURIComponent(rowData.msgid)}`;
    const jsonHeader = {
      headers: {
        "Content-type": "application/json"
      }
    };
    this.$http
      .put(url, JSON.stringify(newVal), jsonHeader)
      .then(() => {
        this.$emit("EditTranslationSuccess");

        kendo.ui.progress($("body"), false);
      })
      .catch(() => {
        this.$emit("EditTranslationFail");
        kendo.ui.progress($("body"), false);
      });
  }
}
