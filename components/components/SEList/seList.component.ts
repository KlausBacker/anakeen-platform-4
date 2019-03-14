import Vue from "vue";
import AnkMixins from "../../mixins/AnkVueComponentMixin";
import * as SeTemplate from "./seListItem.template.kd";
import { Component, Mixins, Prop } from "vue-property-decorator";
import { ISeList } from "./ISeList";

"./ISeList";

declare var kendo;
@Component({
  name: "ank-se-list",
  mixins: [AnkMixins]
})
export default class SeListComponent extends Vue {
  @Prop({ type: String, default: "/CORE/Images/anakeen-logo.svg" })
  public logoUrl;
  @Prop({ type: String, default: "" }) public smartCollection;
  @Prop({ type: String, default: "" }) public label;
  @Prop({
    type: String,
    default: "/components/selist/pager/{collection}/pages/{page}"
  })
  public contentUrl;
  public ps: ISeList;
  @Prop({ type: String, default: "title:asc" }) public order;

  public created() {
    this.ps.privateScope = {
      replaceTopPagerButton: () => {
        const $pager = kendo.jquery(this.$refs.summaryPager);
        const buttons = $pager.find(
          ".k-pager-nav:not(.k-pager-last):not(.k-pager-first)"
        );
        const label = $pager.find("span.k-pager-info");
        label.insertBefore(buttons[1]);
      },

      propageKendoDataSourceEvent: (eventName, eventType = "") => e => {
        const customEvent = this.$createComponentEvent(
          `${eventType}${eventType !== "" ? "-" : ""}se-list-${eventName}`,
          {
            cancelable: eventType === "before",
            detail: [e]
          }
        );
        const notCancelled = this.$emitAnkEvent(
          `se-list-${eventName}`,
          customEvent
        );
        if (eventType === "before" && !notCancelled) {
          if (e.preventDefault) {
            e.preventDefault();
          }
        }
      },

      initKendo: () => {
        const _this = this;
        this.dataSource = new kendo.data.DataSource({
          error: this.ps.privateScope.propageKendoDataSourceEvent("error"),
          requestStart: this.ps.privateScope.propageKendoDataSourceEvent(
            "request",
            "before"
          ),
          requestEnd: this.ps.privateScope.propageKendoDataSourceEvent(
            "request",
            "after"
          ),
          change: this.ps.privateScope.propageKendoDataSourceEvent("change"),
          transport: {
            read: options => {
              if (options.data.collection) {
                const params = {
                  slice: options.data.take,
                  orderBy: this.orderBy,
                  filter: ""
                };
                if (this.filterInput) {
                  params.filter = this.filterInput;
                }

                const request = this.contentUrl
                  .replace("{collection}", options.data.collection)
                  .replace("{page}", options.data.page);
                _this.ps.privateScope
                  .sendGetRequest(request, {
                    params
                  })
                  .then(response => {
                    const apiData = response.data.data;
                    if (
                      apiData &&
                      apiData.collection &&
                      apiData.collection.properties
                    ) {
                      _this.collection = Object.assign(
                        {},
                        _this.collection,
                        apiData.collection.properties
                      );
                    }

                    options.success(response);
                  })
                  .catch(response => {
                    options.error(response);
                  });
              } else {
                options.error();
              }
            }
          },
          pageSize: this.pageSizeOptions[1].value,
          serverPaging: true,
          schema: {
            total: response => response.data.data.resultMax,

            data: response => response.data.data.documents
          }
        });
        kendo.jquery(this.$refs.listView).kendoListView({
          dataSource: this.dataSource,
          template: kendo.template(SeTemplate),
          selectable: "single",
          change: this.ps.privateScope.onSelectSe,
          scrollable: true
        });

        kendo.jquery(this.$refs.pager).kendoPager({
          dataSource: this.dataSource,
          numeric: false,
          input: true,
          info: false,
          pageSizes: false,
          change: this.ps.privateScope.onPagerChange,
          messages: {
            page: "",
            of: "/ {0}",
            empty: this.translations.noDataPagerLabel
          }
        });
        kendo.jquery(this.$refs.summaryPager).kendoPager({
          dataSource: this.dataSource,
          numeric: false,
          input: false,
          info: true,
          change: this.ps.privateScope.onPagerChange,
          messages: {
            display: `{0} - {1} ${this.$pgettext("SEList", "of")} {2}`,
            empty: this.translations.noDataPagerLabel
          }
        });

        kendo
          .jquery(this.$refs.pagerCounter)
          .kendoDropDownList({
            dataSource: this.pageSizeOptions,
            dataTextField: "text",
            dataValueField: "value",
            animation: false,
            index: 1,
            change: this.ps.privateScope.onSelectPageSize,
            headerTemplate: `<li class="dropdown-header">${
              this.translations.itemsPerPageLabel
            }</li>`,
            template:
              '<span class="seList__pagination__pageSize">#= data.text#</span>'
          })
          .data("kendoDropDownList")
          .list.addClass("seList__pagination__list");
      },

      onPagerChange: e => {
        const currentPage = this.dataSource.page();
        const newPage = e.index;
        const customEvent = this.$createComponentEvent(
          "before-se-list-page-change",
          {
            cancelable: true,
            detail: [
              {
                currentPage,
                newPage
              }
            ]
          }
        );
        if (
          this.$emitAnkEvent(
            "before-se-list-page-change",
            customEvent
          )
        ) {
          this.dataSource.page(customEvent.detail[0].newPage);
          this.refreshList()
            .then(() => {
              const customAfterEvent = this.$createComponentEvent(
                "after-se-list-page-change",
                customEvent.detail
              );
              this.$emitAnkEvent(
                "after-se-list-page-change",
                customAfterEvent
              );
            })
            .catch(err => {
              console.error(err);
            });
        }
      },

      sendGetRequest: (url, conf) => {
        const element = kendo.jquery(this.$refs.wrapper);
        kendo.ui.progress(element, true);
        return new Promise((resolve, reject) => {
          this.$http
            .get(url, conf)
            .then(response => {
              kendo.ui.progress(element, false);
              resolve(response);
            })
            .catch(error => {
              kendo.ui.progress(element, false);
              reject(error);
            });
        });
      },

      onSelectPageSize: e => {
        const counter = kendo
          .jquery(this.$refs.pagerCounter)
          .data("kendoDropDownList");
        const newPageSize = counter.dataItem(e.item).value;
        const customEvent = this.$createComponentEvent(
          "before-se-list-pagesize-change",
          {
            cancelable: true,
            detail: [
              {
                newPageSize,
                currentPageSize: this.dataSource.pageSize()
              }
            ]
          },
          e
        );
        if (
          this.$emitAnkEvent(
            "before-se-list-pagesize-change",
            customEvent
          )
        ) {
          this.dataSource.pageSize(customEvent.detail[0].newPageSize);
          this.refreshList()
            .then(() => {
              const customEvent = this.$createComponentEvent(
                "after-se-list-pagesize-change",
                {
                  detail: [
                    {
                      newPageSize,
                      currentPageSize: this.dataSource.pageSize()
                    }
                  ]
                }
              );
              this.$emitAnkEvent(
                "after-se-list-pagesize-change",
                customEvent
              );
            })
            .catch(err => {
              console.error(err);
            });
        }
      },

      onSelectSe: event => {
        const data = this.dataSource.view();
        const listView = kendo
          .jquery(this.$refs.listView)
          .data("kendoListView");
        const selected = $.map(
          listView.select(),
          item => data[$(item).index()]
        );
        this._selectSe(event, selected[0]);
      }
    };
  }
  public mounted() {
    kendo.ui.progress(kendo.jquery(this.$refs.wrapper), true);
    const ready = () => {
      this.ps.privateScope.initKendo();
      this.ps.privateScope.replaceTopPagerButton();
      kendo.ui.progress(kendo.jquery(this.$refs.wrapper), false);

      if (this.smartCollection) {
        this.setCollection({
          title: this.collectionLabel,
          name: this.smartCollection
        }).then(() => {
          this._enableReady();
        });
      } else {
        this._enableReady();
      }
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", ready);
    } else {
      ready();
    }
  }

  public collection: any = null;
  public dataSource: any = null;
  public filterInput: string = "";
  public orderBy: string = this.order;
  public pageSizeOptions: object = [
    {
      text: "5",
      value: 5
    },
    {
      text: "10",
      value: 10
    },
    {
      text: "25",
      value: 25
    },
    {
      text: "50",
      value: 50
    },
    {
      text: "100",
      value: 100
    }
  ];

  public get translations() {
    const searchTranslated = this.$pgettext(
      "SEList",
      "Search in : %{collection}"
    );
    const noDataTranslated = this.$pgettext(
      "SEList",
      "No %{collection} to display"
    );
    return {
      searchPlaceholder: this.$gettextInterpolate(searchTranslated, {
        collection: this.collectionLabel.toUpperCase()
      }),
      itemsPerPageLabel: this.$pgettext("SEList", "Items per page"),
      noDataPagerLabel: this.$gettextInterpolate(noDataTranslated, {
        collection: this.collectionLabel
      })
    };
  }
  public get collectionLabel() {
    if (this.collection && this.collection.title) {
      return this.collection.title;
    } else if (this.label) {
      return this.label;
    } else {
      return "";
    }
  }

  public _onFilterInput(event) {
    const customEvent = this.$createComponentEvent(
      "se-list-filter-input",
      {
        detail: [{ filterInput: this.filterInput }]
      },
      event
    );
    this.$emitAnkEvent("se-list-filter-input", customEvent);
    this.filterInput = customEvent.detail[0].filterInput;
  }

  public _selectSe(event, se) {
    const seProperties = Object.assign({}, se.properties);
    const customEvent = this.$createComponentEvent(
      "se-selected",
      { detail: [seProperties] },
      event
    );
    this.$emitAnkEvent("se-selected", customEvent);
  }
  public filterList(filterValue) {
    this.filterInput = filterValue;
    if (filterValue) {
      return this.refreshList()
        .then()
        .catch(err => {
          console.error(err);
        });
    } else {
      return this.clearListFilter();
    }
  }

  public clearListFilter() {
    this.filterInput = "";
    return this.refreshList()
      .then()
      .catch(err => {
        console.error(err);
      });
  }

  public setCollection(c, opts = null) {
    this.collection = c;
    if (opts && opts.order) {
      this.orderBy = opts.order;
    } else {
      this.orderBy = "title:asc";
    }

    this.dataSource.page(1);
    return this.refreshList()
      .then()
      .catch(err => {
        console.error(err);
      });
  }

  public refreshList() {
    return new Promise((resolve, reject) => {
      if (this.collection && this.dataSource) {
        this.dataSource
          .read({
            collection: this.collection.initid || this.collection.name
          })
          .then(resolve)
          .catch(reject);
      } else {
        reject();
      }
    });
  }
}
