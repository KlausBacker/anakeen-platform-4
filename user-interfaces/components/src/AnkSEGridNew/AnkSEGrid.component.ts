import { Component, Prop, Watch, Vue } from "vue-property-decorator";
import AnkProgress from "./AnkProgress/AnkProgress.vue";
import AnkActionMenu from "./AnkActionMenu/AnkActionMenu.vue";
import AnkTextFilterCell from "./AnkTextFilterCell/AnkTextFilterCell.vue";
import AnkGridCell from "./AnkGridCell/AnkGridCell.vue";
import AnkExportButton from "./AnkExportButton/AnkExportButton.vue";
import AnkGridExpandButton from "./AnkGridExpandButton/AnkGridExpandButton.vue";

import AnkGridPager from "./AnkGridPager/AnkGridPager.vue";
import AnkGridHeaderCell from "./AnkGridHeaderCell/AnkGridHeaderCell.vue";
import { Grid } from "@progress/kendo-vue-grid";
import { VNode } from "vue/types/umd";
import GridActions from "../AnkSEGrid/utils/GridActions";
import GridEvent from "../AnkSEGrid/utils/GridEvent";
import GridError from "../AnkSEGrid/utils/GridError";

const CONTROLLER_URL = "/api/v2/grid/controllers/{controller}/{op}/{collection}";

export interface SmartGridColumn {
  field: string;
  smartType: string;
  title: string;
  abstract: boolean;
  property: boolean;
  encoded: boolean;
  hidden: boolean;
  sortable: boolean;
  filterable: boolean | any;
  transaction: boolean | object;
}

interface SmartGridActions {
  action: string;
  title: string;
  iconClass: string;
}

const DEFAULT_PAGER = {
  buttonCount: 0,
  pageSize: 10,
  pageSizes: [10, 20, 50]
};

const DEFAULT_SORT = {
  mode: "multiple",
  showIndexes: true,
  allowUnsort: true
};

@Component({
  components: {
    "kendo-grid-vue": Grid,
    "ank-progress": AnkProgress,
    "ank-action-menu": AnkActionMenu,
    "ank-export-button": AnkExportButton,
    "ank-expand-button": AnkGridExpandButton,
    "ank-grid-pager": AnkGridPager
  },
  name: "ank-se-grid-vue"
})
export default class GridController extends Vue {
  @Prop({
    default: "",
    type: String
  })
  public collection: string;

  @Prop({
    default: () => [],
    type: Array
  })
  public columns: SmartGridColumn[];
  @Prop({
    default: () => ({}),
    type: Object
  })
  public footer: { [key: string]: any };
  @Prop({
    default: () => [],
    type: Array
  })
  public actions: SmartGridActions[];
  @Prop({
    default: "DEFAULT_GRID_CONTROLLER",
    type: String
  })
  public controller: string;
  @Prop({
    default: "",
    type: String
  })
  public persistStateKey: string;
  @Prop({
    default: true,
    type: Boolean
  })
  public contextTitles: boolean;

  @Prop({
    default: true,
    type: Boolean
  })
  public collapseRowButton: boolean;

  @Prop({
    default: false,
    type: Boolean
  })
  public exportButton: boolean;

  @Prop({
    default: false,
    type: Boolean
  })
  public expandable: boolean;
  @Prop({
    default: "-",
    type: String
  })
  public contextTitlesSeparator: string;

  @Prop({
    default: "",
    type: String
  })
  public emptyCell;
  @Prop({
    default: () => DEFAULT_SORT,
    type: [Boolean, Object]
  })
  public sortable: boolean | object;

  @Prop({
    default: true,
    type: Boolean
  })
  public filterable: boolean;
  @Prop({
    default: false,
    type: Boolean
  })
  public refresh: boolean;
  @Prop({
    default: false,
    type: Boolean
  })
  public reorderable: boolean;

  @Prop({
    default: () => DEFAULT_PAGER,
    type: [Boolean, Object]
  })
  public pageable: boolean | any;

  @Prop({
    default: true,
    type: Boolean
  })
  public resizable: boolean;
  @Prop({
    default: false,
    type: [Boolean, String]
  })
  public selectable: boolean | string;
  @Prop({
    default: false,
    type: Boolean
  })
  public checkable: boolean;

  @Prop({
    default: true,
    type: Boolean
  })
  public persistSelection: boolean;

  @Watch("dataItems")
  public watchDataItems(val) {
    Vue.nextTick(() => {
      val.forEach(item => {
        if (item.selected) {
          // @ts-ignore
          this.$refs.smartGridWidget.$children.forEach(child => {
            if (child.dataItem) {
              if (child.dataItem.properties.id === item.properties.id) {
                child.$el.firstElementChild.firstElementChild.checked = item.selected;
              }
            }
          });
        }
      });
    });
    this.$emit("grid-data-bound", this.gridInstance);
  }

  public transaction: any = null;
  public gridActions: any = null;
  public gridInstance: any = null;
  public gridError: any = null;
  public gridExport: any = null;
  public collectionProperties: any = {};
  public translations = {
    downloadAllResults: "Download all results",
    downloadReport: "Download",
    downloadSelection: "Download selected items",
    downloadAgain: "Retry",
    downloadCancel: "Cancel"
  };
  public onlySelection: boolean = false;
  public columnsList: any = this.columns;
  public actionsList: any = this.actions;
  public dataItems: any = [];
  public selectedRows: any = [];
  public isLoading: boolean = false;
  public currentSort: any = null;
  public currentFilter: any = { logic: "and", filters: [] };
  public currentPage: any = {
    total: null,
    skip: 0,
    take:
      this.pageable && this.pageable !== true
        ? this.pageable.pageSize || DEFAULT_PAGER.pageSize
        : DEFAULT_PAGER.pageSize
  };
  public pager: any = this.pageable === true ? DEFAULT_PAGER : this.pageable;
  public sorter: any = this.sortable === true ? DEFAULT_PAGER : this.sortable;

  public get gridInfo() {
    return {
      columns: this.columns,
      actions: this.actions,
      controller: this.controller,
      collection: this.collection,
      pageable: this.pager,
      page: (this.currentPage.skip + this.currentPage.take) / this.currentPage.take,
      sortable: this.sorter,
      sort: this.currentSort,
      filterable: this.filterable,
      filter: this.currentFilter,
      transaction: this.transaction,
      selectedRows: this.selectedRows,
      onlySelection: this.onlySelection
    };
  }

  public get footerData() {
    if (Object.keys(this.footer).length) {
      return [this.footer];
    }
    return [];
  }

  async created() {
    this.isLoading = true;
    this.gridActions = new GridActions(this);
    this.gridError = new GridError(this);
    this.$on("gridPageChange", this.onPageChange);

    try {
      await this._loadGridConfig();
      await this._loadGridContent();
      this.dataItems = this.dataItems.map(item => {
        return { ...item, selected: false };
      });
      this.isLoading = false;
    } catch (error) {
      console.error(error);
      this.isLoading = false;
    }
  }

  mounted() {
    let saveColumnsOptions = null;
    if (this.persistStateKey) {
      if (window && window.localStorage) {
        saveColumnsOptions = localStorage.getItem(this.persistStateKey);
        if (saveColumnsOptions) {
          saveColumnsOptions = JSON.parse(saveColumnsOptions);
        }
      } else {
        console.error("Persistent grid state is disabled, local storage is not supported by the current environment");
      }
    }
    this.gridInstance = this;
    this.$emit("gridReady");
  }

  public onExpandClicked() {
    // @ts-ignore
    $(this.$refs.smartGridWidget.$el).toggleClass("grid-row-collapsed");
  }

  protected async _loadGridConfig() {
    const url = this._getOperationUrl("config");
    const event = new GridEvent(
      {
        url: url
      },
      null,
      true // Cancelable
    );
    this.$emit("beforeConfig", event);
    if (!event.isDefaultPrevented()) {
      this.$http
        .get(url, {
          params: this.gridInfo
        })
        .then(response => {
          this.columnsList = response.data.data.columns;
          if (response.data.data.pageable === true) {
            this.pager = DEFAULT_PAGER;
          } else if (response.data.data.pageable === false) {
            this.pager = false;
          } else {
            this.pager = Object.assign({}, DEFAULT_PAGER, response.data.data.pageable);
          }
          if (this.checkable) {
            this.columnsList.unshift({
              field: "ank-grid_selected_rows",
              title: " ",
              width: 35,
              headerAttributes: {
                class: "checkable-grid-header grid-cell-align-center toggle-all-rows",
                "data-id": "ank-se-grid-checkable"
              }
            });
          }
          if (response.data.data.actions.length > 0) {
            this.columnsList.push({
              field: "smart_element_grid_action_menu",
              title: " ",
              abstract: true,
              withContext: false,
              sortable: false,
              resizable: false
            });
            this.actionsList = response.data.data.actions;
          }
          const config = response.data.data;
          const responseEvent = new GridEvent(
            {
              config
            },
            null,
            false
          );
          this.$emit("afterConfig", responseEvent);
        })
        .catch(error => {
          console.error(error);
          this.isLoading = false;
        });
    }
  }

  protected async _loadGridContent() {
    const url = this._getOperationUrl("content");
    const event = new GridEvent(
      {
        url: url,
        queryParams: this.gridInfo
      },
      null,
      true // Cancelable
    );
    this.$emit("beforeContent", event);
    if (!event.isDefaultPrevented()) {
      this.$http
        .get(url, {
          params: this.gridInfo
        })
        .then(response => {
          const pager = response.data.data.requestParameters.pager;
          this.currentPage.total = pager.total;
          this.currentPage.skip = pager.skip;
          this.currentPage.take = pager.take;
          this.dataItems = response.data.data.content;
          this.dataItems.forEach(item => {
            item.selected = this.selectedRows.indexOf(item.properties.id) !== -1;
          });
          const responseEvent = new GridEvent(
            {
              content: response.data.data
            },
            null,
            false
          );
          this.$emit("afterContent", responseEvent);
        })
        .catch(error => {
          console.error(error);
          this.isLoading = false;
        });
    }
  }

  protected onSelectionChange(event) {
    this.dataItems.find(item => {
      if (item.properties.id === event.dataItem.properties.id) {
        const checkedValue = event.event.target.checked;
        item.selected = checkedValue;
        if (checkedValue && this.selectedRows.indexOf(item.properties.id) === -1) {
          this.selectedRows.push(event.dataItem.properties.id);
        } else {
          this.selectedRows.splice(this.selectedRows.indexOf(item.properties.id), 1);
        }
      }
    });
    this._loadGridContent();
  }

  protected cellRenderFunction(createElement, tdElement: VNode, props, listeners) {
    const columnConfig = this.columnsList[props.columnIndex];
    const event = new GridEvent(
      {
        rowData: props.dataItem,
        columnConfig: columnConfig,
      },
      null,
      false,
      "GridCellRenderEvent"
    );
    this.$emit("beforeGridCellRender", event);
    let renderElement = tdElement;
    if (props.field === "smart_element_grid_action_menu") {
      if (this.actionsList.length > 0) {
        renderElement = createElement(AnkActionMenu, {
          props: {
            actions: this.actionsList,
            rowData: props.dataItem,
            gridComponent: this
          },
          on: {
            rowActionClick: (...args) => this.$emit("rowActionClick", ...args)
          }
        });
        if (this.$scopedSlots && this.$scopedSlots.actionTemplate) {
          return this.$scopedSlots.actionTemplate({
            renderElement,
            props,
            listeners,
            columnConfig,
            actions: this.actionsList
          });
        }
      }
    } else if (props.field === "ank-grid_selected_rows") {
      return renderElement;
    } else {
      if (columnConfig) {
        renderElement = createElement(AnkGridCell, {
          props: {
            ...props,
            columnConfig
          }
        });
      }
      if (this.$scopedSlots && this.$scopedSlots.cellTemplate) {
        return this.$scopedSlots.cellTemplate({
          renderElement,
          props,
          listeners,
          columnConfig
        });
      }
    }
    return renderElement;
  }

  protected headerCellRenderFunction(createElement, defaultRendering, props, listeners) {
    const columnConfig = this.columnsList.find(c => c.field === props.field);
    return createElement(AnkGridHeaderCell, {
      props: { ...props, columnConfig, grid: this },
      on: {
        sortchange: this.onSortChange,
        filterchange: this.onFilterChange
      }
    });
  }

  protected _getOperationUrl(operation) {
    return CONTROLLER_URL.replace(/\{(\w+)\}/g, (match, substr, ...args) => {
      switch (substr) {
        case "controller":
          return this.controller;
        case "op":
          return operation;
        case "collection":
          return this.collection;
        default:
          return substr;
      }
    });
  }

  protected async onSortChange(sortEvt) {
    const sort = sortEvt.sort;
    this.currentSort = sortEvt.sort;
    this.isLoading = true;
    await this._loadGridContent();
    this.isLoading = false;
  }

  protected async onPageChange(pagerEvt) {
    this.currentPage = Object.assign({}, this.currentPage, pagerEvt.page);
    this.pager = Object.assign({}, this.pager, { pageSize: pagerEvt.page.take });
    this.isLoading = true;
    await this._loadGridContent();
    this.isLoading = false;
  }

  protected async onFilterChange(filterEvt) {
    if (filterEvt) {
      const filters = this.currentFilter.filters.filter(f => f.field !== filterEvt.field);
      if (filterEvt.filters) {
        filters.push(filterEvt);
      }
      this.currentFilter.filters = filters;
    }
    this.isLoading = true;
    await this._loadGridContent();
    this.isLoading = false;
  }

  protected onColumnReorder(reorderEvt) {
    console.log(reorderEvt);
  }

  protected export(
    exportAll = true,
    directDownload = true,
    onPolling = () => {},
    pollingTime = 500,
    onExport = this.doDefaultExport.bind(this)
  ) {
    let beforeEvent = this.sendBeforeExportEvent(onExport, onPolling);
    if (!beforeEvent.isDefaultPrevented()) {
      let exportCb = beforeEvent.onExport;
      let pollingCb = beforeEvent.onPolling;
      if (typeof onExport === "function") {
        this.sendBeforePollingEvent();
        let promise = this.createExportTransaction()
          .then(transaction => {
            this.transaction = transaction;

            return this.doTransactionExport(
              transaction,
              this.gridInfo,
              exportCb,
              pollingCb,
              pollingTime,
              directDownload
            );
          })
          .then(result => {
            return result ? result.data : true;
          });
        if (!directDownload) {
          return promise;
        }
      } else {
        this.gridError.error("Export failed: no export function are provided");
        this.sendErrorEvent("Export failed: no export function are provided");
      }
    }
  }

  protected async doDefaultExport(transaction, queryParams, directDownload) {
    const exportUrl = this._getOperationUrl("export");
    await this.$http
      .get(exportUrl, {
        params: this.gridInfo,
        responseType: "blob"
      })
      .then(response => this.downloadExportFile(response.data))
      .catch(err => {
        this.gridError.error(err);
        this.sendErrorEvent(err);
      });
  }

  protected downloadExportFile(blobFile) {
    const blob = new Blob([blobFile], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
    const url = window.URL.createObjectURL(blob);
    let link;
    const existLink = $("a.seGridExportLink");
    if (existLink.length) {
      link = existLink[0];
    } else {
      link = document.createElement("a");
      link.classList.add("seGridExportLink");

      document.body.appendChild(link);
    }
    link.setAttribute("download", `${this.collectionProperties.title || this.collection || "data"}.xlsx`);
    link.href = url;
    link.click();
  }

  protected sendBeforeExportEvent(onExport, onPolling) {
    const event = new GridEvent({
      component: this,
      type: "export"
    });
    event.onExport = onExport;
    event.onPolling = onPolling;
    this.$emit("beforeGridExport", event);
    return event;
  }

  protected sendBeforePollingEvent() {
    const event = new GridEvent(null, null, false);
    this.$emit("beforePollingGridExport", event);
    return event;
  }

  protected sendErrorEvent(message) {
    const event = new GridEvent(
      {
        message: message
      },
      null,
      false
    );
    this.$emit("grid-export-error", event);
    return event;
  }

  protected createExportTransaction() {
    return this.$http
      .post("/api/v2/grid/export")
      .then(response => {
        return response.data.data;
      })
      .catch(err => {
        this.gridError.error(err);
        this.sendErrorEvent(err);
      });
  }

  protected doTransactionExport(transaction, queryParams, exportRequest, pollingRequest, pollingTime, directDownload) {
    const transactionId = transaction.transactionId;
    let file = exportRequest(transaction, queryParams, directDownload);
    this.pollTransaction(transactionId, pollingRequest, pollingTime);

    return file;
  }

  protected pollTransaction(transactionId, pollingCb, pollingTime) {
    let timer = null;
    const getStatus = () => {
      this.$http
        .get(`/api/v2/ui/transaction/${transactionId}/status`)
        .then(response => {
          const responseData = response.data.data;
          if (responseData.transactionStatus === "PENDING" || responseData.transactionStatus === "CREATED") {
            if (typeof pollingCb === "function") {
              pollingCb(responseData);
            }
            timer = setTimeout(getStatus, pollingTime);
          } else {
            if (typeof pollingCb === "function") {
              pollingCb(responseData);
            }
            if (timer) {
              clearTimeout(timer);
            }
          }
        })
        .catch(err => {
          console.error(err);
          if (timer) {
            clearTimeout(timer);
          }
        });
    };
    getStatus();
  }
}
