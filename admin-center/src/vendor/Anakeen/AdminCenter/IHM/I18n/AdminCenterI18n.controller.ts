import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";

Vue.use(ButtonsInstaller);
declare var kendo;
@Component
export default class I18nManagerController extends Vue {
  private static isEmptyOrSpaces(str) {
    return str === null || str.match(/^\s*$/) !== null;
  }
  private singularInput: string = `<div class="input-group">
                <input type='text' placeholder="modifier la traduction" class='form-control overriden-translation-input-singular filter-locale' aria-label='Small'>
                <div class="input-group-append">
                    <button class='confirm-override-translation-singular btn btn-outline-secondary' disabled><i class='fa fa-check'></i></button>
                    <button class='cancel-override-translation-singular btn btn-outline-secondary' disabled><i class='fa fa-times'></i></button>
                </div>
            </div>`;
  private pluralInput: string = `<div class="input-group">
                <input type='text' placeholder="modifier la traduction pluriel" class='form-control overriden-translation-input-plural filter-locale' aria-label='Small'>
                <div class="input-group-append">
                    <button class='confirm-override-translation-plural btn btn-outline-secondary'disabled><i class='fa fa-check'></i></button>
                    <button class='cancel-override-translation-plural btn btn-outline-secondary' disabled><i class='fa fa-times'></i></button>
                </div>
            </div>`;
  private translationLocale: string = "fr";
  private translationFilterableOptions: kendo.data.DataSourceFilter = {
    cell: {
      minLength: 3,
      operator: "contains",
      showOperators: false
    }
  };
  private translationGridData: kendo.data.DataSource = new kendo.data.DataSource(
    {
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
        $(".overriden-translation-input").attr(
          "placeholder",
          "modifier la traduction"
        );
      } else {
        $(".overriden-translation-input").attr(
          "placeholder",
          "edit translation"
        );
      }
    }, 300);
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
          title: "ID"
        },
        {
          field: "msgstr",
          filterable: this.translationFilterableOptions,
          minResizableWidth: 25,
          template: rowData => {
            if (rowData.plurals) {
              let cellData = "";
              // tslint:disable-next-line:prefer-for-of
              for (let i = 0; i < rowData.plurals.length - 1; i++) {
                cellData += rowData.plurals[i] + "<hr>";
              }
              cellData += rowData.plurals[rowData.plurals.length - 1];
              return cellData;
            } else {
              return rowData.msgstr;
            }
          },
          title: "Server translation"
        },
        {
          field: "overridentranslation",
          filterable: this.translationFilterableOptions,
          minResizableWidth: 25,
          template: rowData => {
            if (rowData.pluralid) {
              if (rowData.override) {
                if (
                  !I18nManagerController.isEmptyOrSpaces(rowData.override[0]) &&
                  !I18nManagerController.isEmptyOrSpaces(rowData.override[1])
                ) {
                  if (rowData.override[0] && rowData.override[1]) {
                    return `<span class='override-singular-value-exist'>${
                      rowData.override[0]
                      }</span><hr>
                          <span class='override-plural-value-exist'>${
                      rowData.override[1]
                      }</span>`;
                  } else if (rowData.override[0] && !rowData.override[1]) {
                    return (
                      `<span class='override-singular-value-exist'>${
                        rowData.override[0]
                        }</span>` + this.pluralInput
                    );
                  } else if (rowData.override[1] && !rowData.override[0]) {
                    return (
                      this.pluralInput +
                      `<span class='override-plural-value-exist'>${
                        rowData.override[1]
                        }</span>`
                    );
                  } else {
                    return this.singularInput + this.pluralInput;
                  }
                } else {
                  const val0 = I18nManagerController.isEmptyOrSpaces(
                    rowData.override[0]
                  );
                  const val1 = I18nManagerController.isEmptyOrSpaces(
                    rowData.override[1]
                  );
                  if (val0 && val1) {
                    return this.singularInput + this.pluralInput;
                  } else if (val0 && !val1) {
                    return (
                      this.singularInput +
                      `<hr>
                      <span class='override-plural-value-exist'>${
                        rowData.override[1]
                        }</span>`
                    );
                  } else if (!val0 && val1) {
                    return (
                      `
                      <span class='override-singular-value-exist'>${
                        rowData.override[0]
                        }</span><hr>` + this.pluralInput
                    );
                  } else {
                    return `<span class='override-singular-value-exist'>${
                      rowData.override[0]
                      }</span><hr>
                          <span class='override-plural-value-exist'>${
                      rowData.override[1]
                      }</span>`;
                  }
                }
              } else {
                return this.singularInput + this.pluralInput;
              }
            } else {
              if (rowData.override) {
                return `<span class='override-singular-value-exist'>${
                  rowData.override
                  }</span>`;
              } else {
                return this.singularInput;
              }
            }
          },
          title: "Overriden translation"
        }
      ],
      dataBound: e => {
        this.setEventSingularSpan();
        this.setEventPluralSpan();
        this.setEventInput();
        this.setEventSingularConfirm();
        this.setEventPluralConfirm();
        this.setEventSingularCancel();
        this.setEventPluralCancel();
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
    importBtn.on("change", () => {
      const formData = new FormData();
      formData.append(
        "file",
        (this.$refs.importFile as HTMLInputElement).files[0]
      );
      this.$http
        .post(
          `/api/v2/admin/i18n/${encodeURIComponent(this.translationLocale)}/`,
          formData,
          {
            headers: {
              "Content-Type": "multipart/form-data"
            }
          }
        )
        .then(() => {
          $(this.$refs.i18nGrid)
            .data("kendoGrid")
            .dataSource.read();
        });
    });
  }

  public exportLocaleFile() {
    window.open(
      `/api/v2/admin/i18n/${this.translationLocale}/custom.po`,
      "_self"
    );
  }

  private initInput(target, type) {
    if (type === "singular") {
      $(target).replaceWith(this.singularInput);
      this.setEventSingularCancel();
      this.setEventSingularConfirm();
      this.setEventInput();
    } else {
      $(target).replaceWith(this.pluralInput);
      this.setEventPluralCancel();
      this.setEventPluralConfirm();
      this.setEventInput();
    }
  }

  private setEventPluralSpan() {
    $(".override-plural-value-exist").on("click", e => {
      this.initInput(e.target, "plural");
    });
  }

  private setEventSingularSpan() {
    $(".override-singular-value-exist").on("click", e => {
      this.initInput(e.target, "singular");
    });
  }

  private setEventSingularConfirm() {
    $(".confirm-override-translation-singular").kendoButton({
      click: confirmEvent => {
        kendo.ui.progress($("body"), true);
        const rowData: any = $(this.$refs.i18nGrid)
          .data("kendoGrid")
          .dataItem($(confirmEvent.event.target).closest("tr[role=row]"));
        let newVal;
        if (rowData.pluralid) {
          newVal = JSON.stringify({
            msgstr: $(confirmEvent.event.target.closest("tr[role=row]")).find(
              "input"
            )[0].value,
            plural: 0,
            pluralid: rowData.pluralid
          });
        } else {
          newVal = JSON.stringify({
            msgstr: $(confirmEvent.event.target.closest("tr[role=row]")).find(
              "input"
            )[0].value
          });
        }
        const msgctxtData = rowData.msgctxt !== null ? rowData.msgctxt : "";
        const url = `/api/v2/admin/i18n/${encodeURIComponent(
          this.translationLocale
        )}/${encodeURIComponent(msgctxtData)}/${encodeURIComponent(
          rowData.msgid
        )}`;
        const jsonHeader = {
          headers: {
            "Content-type": "application/json"
          }
        };
        this.$http.put(url, newVal, jsonHeader).then(response => {
          if (response.status === 200) {
            this.$emit("EditTranslationSuccess");
          } else {
            this.$emit("EditTranslationFail");
          }
          $(confirmEvent.sender.element[0])
            .data("kendoButton")
            .enable(false);
          $(confirmEvent.sender.element[0].nextElementSibling)
            .data("kendoButton")
            .enable(false);
          kendo.ui.progress($("body"), false);
          if (
            !I18nManagerController.isEmptyOrSpaces(JSON.parse(newVal).msgstr)
          ) {
            $(
              confirmEvent.sender.element[0].closest("div[class='input-group']")
            ).replaceWith(
              `<span class='override-singular-value-exist'>${
                JSON.parse(newVal).msgstr
                }</span>`
            );
            this.setEventSingularSpan();
          }
        });
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

        const msgctxtData = rowData.msgctxt !== null ? rowData.msgctxt : "";
        const inputVal =
          $(confirmEvent.event.target.closest("tr[role=row]")).find("input")
            .length > 1
            ? $(confirmEvent.event.target.closest("tr[role=row]")).find(
            "input"
            )[1].value
            : $(confirmEvent.event.target.closest("tr[role=row]")).find(
            "input"
            )[0].value;
        const newVal = JSON.stringify({
          msgstr: inputVal,
          plural: 1,
          pluralid: rowData.pluralid
        });

        const url = `/api/v2/admin/i18n/${encodeURIComponent(
          this.translationLocale
        )}/${encodeURIComponent(msgctxtData)}/${encodeURIComponent(
          rowData.msgid
        )}`;
        const jsonHeader = {
          headers: {
            "Content-type": "application/json"
          }
        };
        this.$http.put(url, newVal, jsonHeader).then(response => {
          if (response.status === 200) {
            this.$emit("EditTranslationSuccess");
          } else {
            this.$emit("EditTranslationFail");
          }
          $(confirmEvent.sender.element[0])
            .data("kendoButton")
            .enable(false);
          $(confirmEvent.sender.element[0].nextElementSibling)
            .data("kendoButton")
            .enable(false);
          kendo.ui.progress($("body"), false);
          if (
            !I18nManagerController.isEmptyOrSpaces(JSON.parse(newVal).msgstr)
          ) {
            $(
              confirmEvent.sender.element[0].closest("div[class='input-group']")
            ).replaceWith(
              `<span class='override-plural-value-exist'>${
                JSON.parse(newVal).msgstr
                }</span>`
            );
            if ($(confirmEvent.sender.element[0]).prev("hr")) {
              $(".override-plural-value-exist").prepend("<hr>");
            }
            this.setEventPluralSpan();
          }
        });
      }
    });
  }

  private setEventSingularCancel() {
    $(".cancel-override-translation-singular").kendoButton({
      click: cancelEvent => {
        $(cancelEvent.sender.element[0].previousElementSibling)
          .data("kendoButton")
          .enable(false);
        $(cancelEvent.sender.element[0])
          .data("kendoButton")
          .enable(false);
        $(this.$refs.i18nGrid)
          .data("kendoGrid")
          .dataSource.read();
      }
    });
  }

  private setEventPluralCancel() {
    $(".cancel-override-translation-plural").kendoButton({
      click: cancelEvent => {
        $(cancelEvent.sender.element[0].previousElementSibling)
          .data("kendoButton")
          .enable(false);
        $(cancelEvent.sender.element[0])
          .data("kendoButton")
          .enable(false);
        $(this.$refs.i18nGrid)
          .data("kendoGrid")
          .dataSource.read();
      }
    });
  }

  private setEventInput() {
    $(
      ".overriden-translation-input-singular, .overriden-translation-input-plural"
    ).on("input", event => {
      const cancelBtn = $(event.target.nextElementSibling.children[1]);
      const confirmBtn = $(event.target.nextElementSibling.children[0]);
      confirmBtn.data("kendoButton").enable(true);
      cancelBtn.data("kendoButton").enable(true);
    });
  }
}
