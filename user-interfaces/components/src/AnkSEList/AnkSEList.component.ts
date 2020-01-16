import "@progress/kendo-ui/js/kendo.dropdownlist";
import "@progress/kendo-ui/js/kendo.pager";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import EventUtilsMixin from "../../mixins/AnkVueComponentMixin/EventUtilsMixin";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";
import ReadyMixin from "../../mixins/AnkVueComponentMixin/ReadyMixin";

@Component({
  name: "ank-se-list"
})
export default class SeListComponent extends Mixins(EventUtilsMixin, ReadyMixin, I18nMixin) {
  public get dataSourceItems() {
    if (this.dataSource) {
      const view = this.dataSource.view();
      if (view.length) {
        const customEvent = this.$createEvent(`se-list-dataBound`, {
          cancelable: false,
          data: [view.toJSON()]
        });
        this.$emit(`se-list-dataBound`, customEvent);
        return view.toJSON();
      }
    }
    return [];
  }
  public get translations() {
    const searchTranslated = this.$t("selist.Search in : {collection}", {
      collection: this.collectionLabel.toUpperCase()
    });
    const noDataTranslated = this.$t("selist.No {collection} to display", { collection: this.collectionLabel });
    return {
      itemsPerPageLabel: this.$t("selist.Items per page"),
      noDataPagerLabel: noDataTranslated,
      searchPlaceholder: searchTranslated
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
  @Prop({ type: String, default: "/CORE/Images/anakeen-logo.svg" })
  public logoUrl;
  @Prop({ type: String, default: "" }) public smartCollection;
  @Prop({ type: String, default: "" }) public label;
  @Prop({
    default: "/components/selist/pager/{collection}/pages/{page}",
    type: String
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
    "is-compact": false,
    "is-tiny": false,
    seList__wrapper: true
  };

  @Watch("page")
  public onPagePropChange(newVal) {
    this.dataSource.page(newVal);
    this.refreshList();
  }

  @Watch("filterInput")
  public onFilterInputDataChange(newVal, oldVal) {
    const customEvent = this.$createEvent("se-list-filter-input", {
      data: [{ filterInput: newVal, oldFilterInput: oldVal }]
    });
    this.$emit("se-list-filter-input", customEvent);
  }

  public created() {
    this.initDataSource();
  }
  public mounted() {
    kendo.ui.progress(kendo.jQuery(this.$refs.wrapper), true);
    const ready = () => {
      if (this.$_globalI18n.loaded) {
        this.initWidgets();
      } else {
        this.$on("localeLoaded", () => {
          this.initWidgets();
        });
      }

      this.onResize();
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

  public filterList(filterValue) {
    const customEvent = this.$createEvent("se-list-filter-change", {
      data: [{ filterInput: filterValue }]
    });
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
    const customEvent = this.$createEvent("se-list-filter-change", {
      data: [{ filterInput: "" }]
    });
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
      // tslint:disable-next-line:triple-equals
      const seSelected = this.dataSourceItems.find(i => i.properties.initid == seId);
      if (seSelected) {
        const customEvent = this.$createEvent("se-selected", { data: [seSelected.properties] });
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
      this.orderBy = this.order;
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
  protected initWidgets() {
    this.initKendoWidgets();
    window.addEventListener("resize", this.onResize);
    kendo.ui.progress(kendo.jQuery(this.$refs.wrapper), false);
    if (this.smartCollection) {
      this.setCollection({
        name: this.smartCollection,
        title: this.collectionLabel
      }).then(() => {
        this._enableReady();
      });
    } else {
      this._enableReady();
    }
  }
  protected onResize() {
    if (this.$el.clientWidth) {
      this.componentClasses["is-compact"] = this.$el.clientWidth < 210;
      this.componentClasses["is-tiny"] = this.$el.clientWidth < 170;
    }
  }

  protected onClickSE(item) {
    const customEvent = this.$createEvent("se-clicked", { data: [item.properties] });
    this.$emit("se-clicked", customEvent);
    this.selectSe(item.properties.initid);
  }

  protected propageKendoDataSourceEvent(eventName, eventType = "") {
    return e => {
      const customEvent = this.$createEvent(`${eventType}${eventType !== "" ? "-" : ""}se-list-${eventName}`, {
        cancelable: eventType === "before",
        data: [e]
      });
      this.$emit(`se-list-${eventName}`, customEvent);
      if (eventType === "before" && customEvent.isDefaultPrevented()) {
        if (e.preventDefault) {
          e.preventDefault();
        }
      }
    };
  }

  protected initDataSource() {
    // tslint:disable-next-line:variable-name
    const _this = this;
    this.dataSource = new kendo.data.DataSource({
      change: this.propageKendoDataSourceEvent("change"),
      error: this.propageKendoDataSourceEvent("error"),
      page: this.page,
      pageSize: this.pageSizeOptions[1].value,
      requestEnd: this.propageKendoDataSourceEvent("request", "after"),
      requestStart: this.propageKendoDataSourceEvent("request", "before"),
      schema: {
        total: response => response.data.data.resultMax,

        data: response => response.data.data.documents
      },
      serverPaging: true,
      transport: {
        read: options => {
          if (options.data.collection) {
            const params = {
              filter: "",
              orderBy: this.orderBy,
              slice: options.data.take
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
                if (apiData && apiData.collection && apiData.collection.properties) {
                  _this.collection = Object.assign({}, _this.collection, apiData.collection.properties);
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
      }
    });
  }

  protected initKendoWidgets() {
    kendo.jQuery(this.$refs.pager).kendoPager({
      change: this.onPagerChange,
      dataSource: this.dataSource,
      info: false,
      input: true,
      messages: {
        empty: this.translations.noDataPagerLabel as string,
        of: "/ {0}",
        page: ""
      },
      numeric: false,
      pageSizes: false
    });

    kendo
      .jQuery(this.$refs.pagerCounter)
      .kendoDropDownList({
        animation: false,
        change: this.onSelectPageSize,
        dataSource: this.pageSizeOptions,
        dataTextField: "text",
        dataValueField: "value",
        headerTemplate: `<li class="dropdown-header">${this.translations.itemsPerPageLabel}</li>`,
        index: 1,
        template: '<span class="seList__pagination__pageSize">#= data.text#</span>'
      })
      .data("kendoDropDownList")
      .list.addClass("seList__pagination__list");
  }

  protected onPagerChange(e) {
    const currentPage = this.dataSource.page();
    const newPage = e.index;
    const customEvent = this.$emitCancelableEvent("before-se-list-page-change", {
      currentPage,
      newPage
    });
    if (!customEvent.isDefaultPrevented()) {
      this.dataSource.page(customEvent.detail[0].newPage);
      this.refreshList()
        .then(() => {
          const customAfterEvent = this.$createEvent("after-se-list-page-change", {
            cancelable: false,
            data: customEvent.data
          });
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
    const counter = kendo.jQuery(this.$refs.pagerCounter).data("kendoDropDownList");
    const newPageSize = counter.dataItem(e.item).value;
    const customEvent = this.$emitCancelableEvent("before-se-list-pagesize-change", {
      currentPageSize: this.dataSource.pageSize(),
      newPageSize
    });
    if (!customEvent.isDefaultPrevented()) {
      this.dataSource.pageSize(customEvent.detail[0].newPageSize);
      this.refreshList()
        .then(() => {
          const customAfterEvent = this.$createEvent("after-se-list-pagesize-change", {
            data: [
              {
                currentPageSize: this.dataSource.pageSize(),
                newPageSize
              }
            ]
          });
          this.$emit("after-se-list-pagesize-change", customAfterEvent);
        })
        .catch(err => {
          console.error(err);
        });
    }
  }
}
