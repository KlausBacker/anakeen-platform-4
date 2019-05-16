import Vue from "vue";
import VueSetup from "../setup.js";
import { Component, Prop, Watch } from "vue-property-decorator";
import {
  $createComponentEvent,
  _enableReady
} from "../../mixins/AnkVueComponentMixin/IeventUtilsMixin";

Vue.use(VueSetup);
@Component({
  name: "ank-se-list"
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
  @Prop({ type: String, default: "title:asc" }) public order;
  @Prop({ type: Number, default: 1 }) public page;
  @Prop({ type: String, default: "Aucun contenu" }) public emptyMessage;
  @Prop({ type: Boolean, default: true }) public selectable;

  public $refs!: {
    wrapper: HTMLElement;
    pager: HTMLElement;
    pagerCounter: HTMLElement;
  };

  @Watch("page")
  onPagePropChange(newVal) {
    this.dataSource.page(newVal);
    this.refreshList();
  }

  @Watch("filterInput")
  onFilterInputDataChange(newVal, oldVal) {
    const customEvent = $createComponentEvent(
      "se-list-filter-input",
      {
        detail: [{ filterInput: newVal, oldFilterInput: oldVal }]
      },
      event
    );
    this.$emit("se-list-filter-input", customEvent);
  }

  public created() {
    this.initDataSource();
  }
  public mounted() {
    kendo.ui.progress(kendo.jQuery(this.$refs.wrapper), true);
    const ready = () => {
      this.initKendoWidgets();
      window.addEventListener("resize", this.onResize);
      kendo.ui.progress(kendo.jQuery(this.$refs.wrapper), false);

      if (this.smartCollection) {
        this.setCollection({
          title: this.collectionLabel,
          name: this.smartCollection
        }).then(() => {
          _enableReady();
        });
      } else {
        _enableReady();
      }
    };

    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", ready);
    } else {
      ready();
    }
  }

  public destroyed() {
    window.removeEventListener("resize", this.onResize);
  }

  public collection: any = null;
  public dataSource: kendo.data.DataSource = null;
  public selectedItem: string | number = "";
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
  public componentClasses = {
    seList__wrapper: true,
    "is-compact": false,
    "is-tiny": false
  };

  public get dataSourceItems() {
    if (this.dataSource) {
      const view = this.dataSource.view();
      if (view.length) {
        const customEvent = $createComponentEvent(`se-list-dataBound`, {
          cancelable: false,
          detail: [view.toJSON()]
        });
        this.$emit(`se-list-dataBound`, customEvent);
        return view.toJSON();
      }
    }
    return [];
  }
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
    if (this.label) {
      return this.label;
    } else if (this.collection && this.collection.title) {
      return this.collection.title;
    } else {
      return "";
    }
  }

  public filterList(filterValue) {
    const customEvent = $createComponentEvent(
      "se-list-filter-change",
      {
        detail: [{ filterInput: filterValue }]
      },
      event
    );
    this.$emit("se-list-filter-change", customEvent);
    this.filterInput = filterValue;
    if (filterValue) {
      this.dataSource.page(1);
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
    const customEvent = $createComponentEvent(
      "se-list-filter-change",
      {
        detail: [{ filterInput: "" }]
      },
      event
    );
    this.$emit("se-list-filter-change", customEvent);
    this.$emit("se-list-filter-clear", customEvent);
    this.filterInput = "";
    return this.refreshList()
      .then()
      .catch(err => {
        console.error(err);
      });
  }

  public selectSe(seId) {
    if (this.selectable) {
      const seSelected = this.dataSourceItems.find(
        i => i.properties.initid == seId
      );
      if (seSelected) {
        const customEvent = $createComponentEvent(
          "se-selected",
          { detail: [seSelected.properties] },
          event
        );
        this.$emit("se-selected", customEvent);
        this.selectedItem = seId;
        this.$nextTick(() => {
          this.scrollToActiveItem();
        });
      }
    }
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

  public scrollToActiveItem() {
    const activeItem = this.$el.querySelector(".is-active");
    if (activeItem) {
      activeItem.scrollIntoView();
    }
  }

  protected onResize() {
    if (this.$el.clientWidth) {
      this.componentClasses["is-compact"] = this.$el.clientWidth < 210;
      this.componentClasses["is-tiny"] = this.$el.clientWidth < 170;
    }
  }

  protected onClickSE(item) {
    const customEvent = $createComponentEvent(
      "se-clicked",
      { detail: [item.properties] },
      event
    );
    this.$emit("se-clicked", customEvent);
    this.selectSe(item.properties.initid);
  }

  protected propageKendoDataSourceEvent(eventName, eventType = "") {
    return e => {
      const customEvent = $createComponentEvent(
        `${eventType}${eventType !== "" ? "-" : ""}se-list-${eventName}`,
        {
          cancelable: eventType === "before",
          detail: [e]
        }
      );
      this.$emit(`se-list-${eventName}`, customEvent);
      if (eventType === "before" && customEvent.defaultPrevented) {
        if (e.preventDefault) {
          e.preventDefault();
        }
      }
    };
  }

  protected initDataSource() {
    const _this = this;
    this.dataSource = new kendo.data.DataSource({
      error: this.propageKendoDataSourceEvent("error"),
      requestStart: this.propageKendoDataSourceEvent("request", "before"),
      requestEnd: this.propageKendoDataSourceEvent("request", "after"),
      change: this.propageKendoDataSourceEvent("change"),
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
            _this
              .sendGetRequest(request, {
                params
              })
              .then(response => {
                // @ts-ignore
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
      page: this.page,
      serverPaging: true,
      schema: {
        total: response => response.data.data.resultMax,

        data: response => response.data.data.documents
      }
    });
  }

  protected initKendoWidgets() {
    kendo.jQuery(this.$refs.pager).kendoPager({
      dataSource: this.dataSource,
      numeric: false,
      input: true,
      info: false,
      pageSizes: false,
      change: this.onPagerChange,
      messages: {
        page: "",
        of: "/ {0}",
        empty: this.translations.noDataPagerLabel
      }
    });

    kendo
      .jQuery(this.$refs.pagerCounter)
      .kendoDropDownList({
        dataSource: this.pageSizeOptions,
        dataTextField: "text",
        dataValueField: "value",
        animation: false,
        index: 1,
        change: this.onSelectPageSize,
        headerTemplate: `<li class="dropdown-header">${
          this.translations.itemsPerPageLabel
        }</li>`,
        template:
          '<span class="seList__pagination__pageSize">#= data.text#</span>'
      })
      .data("kendoDropDownList")
      .list.addClass("seList__pagination__list");
  }

  protected onPagerChange(e) {
    const currentPage = this.dataSource.page();
    const newPage = e.index;
    const customEvent = $createComponentEvent("before-se-list-page-change", {
      cancelable: true,
      detail: [
        {
          currentPage,
          newPage
        }
      ]
    });
    this.$emit("before-se-list-page-change", customEvent);
    if (!customEvent.defaultPrevented) {
      this.dataSource.page(customEvent.detail[0].newPage);
      this.refreshList()
        .then(() => {
          const customAfterEvent = $createComponentEvent(
            "after-se-list-page-change",
            {
              detail: customEvent.detail
            }
          );
          this.$emit("after-se-list-page-change", customAfterEvent);
        })
        .catch(err => {
          console.error(err);
        });
    }
  }

  protected sendGetRequest(url, conf) {
    const element = kendo.jQuery(this.$refs.wrapper);
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
  }

  protected onSelectPageSize(e) {
    const counter = kendo
      .jQuery(this.$refs.pagerCounter)
      .data("kendoDropDownList");
    const newPageSize = counter.dataItem(e.item).value;
    const customEvent = $createComponentEvent(
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
    this.$emit("before-se-list-pagesize-change", customEvent);
    if (!customEvent.defaultPrevented) {
      this.dataSource.pageSize(customEvent.detail[0].newPageSize);
      this.refreshList()
        .then(() => {
          const customEvent = $createComponentEvent(
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
          this.$emit("after-se-list-pagesize-change", customEvent);
        })
        .catch(err => {
          console.error(err);
        });
    }
  }
}
