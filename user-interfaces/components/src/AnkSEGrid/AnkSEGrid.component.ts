import { Component, Prop, Watch, Mixins, Vue } from "vue-property-decorator";
import AnkActionMenu from "./AnkActionMenu/AnkActionMenu.vue";
import AnkGridCell from "./AnkGridCell/AnkGridCell.vue";
import AnkExportButton from "./AnkExportButton/AnkExportButton.vue";
import AnkGridExpandButton from "./AnkGridExpandButton/AnkGridExpandButton.vue";
import AnkGridColumnsButton from "./AnkGridColumnsButton/AnkGridColumnsButton.vue";

import AnkGridPager from "./AnkGridPager/AnkGridPager.vue";
import AnkGridHeaderCell from "./AnkGridHeaderCell/AnkGridHeaderCell.vue";
import { Grid, GridNoRecords } from "@progress/kendo-vue-grid";
import { VNode } from "vue/types/umd";
import GridEvent from "./AnkGridEvent/AnkGridEvent";
import GridError from "./utils/GridError";
import GridExportEvent from "./AnkGridEvent/AnkGridExportEvent";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";

import $ from "jquery";

const CONTROLLER_URL = "/api/v2/grid/controllers/{controller}/{op}/{collection}";

export interface SmartGridColumn {
  withContext?: boolean;
  width?: number;
  headerAttributes?: { [key: string]: string };
  field: string;
  smartType?: string;
  title?: string;
  abstract?: boolean;
  multiple?: boolean;
  property?: boolean;
  encoded?: boolean;
  hidden?: boolean;
  sortable?: boolean;
  filterable?: boolean | object;
  transaction?: boolean | object;
  resizable?: boolean;
}

export interface SmartGridAction {
  action: string;
  title: string;
  iconClass: string;
}

export type SmartGridCellPropertyValue = string | { [key: string]: string | number | boolean } | number | boolean;
export interface SmartGridCellFieldValue {
  value: string | number | boolean;
  displayValue: string;
}
export type SmartGridCellAbstractValue = string | object | number | boolean | SmartGridCellFieldValue;

export type SmartGridCellValue =
  | SmartGridCellAbstractValue
  | SmartGridCellFieldValue
  | SmartGridCellFieldValue[]
  | SmartGridCellPropertyValue;

export interface SmartGridRowData {
  properties: {
    [key: string]: SmartGridCellPropertyValue;
  };
  attributes: {
    [key: string]: SmartGridCellFieldValue;
  };
  abstract?: {
    [key: string]: SmartGridCellAbstractValue;
  };
  selected?: boolean;
}

export interface SmartGridPageSize {
  page: number;
  pageSize: number;
  total: number;
}

export interface SmartGridSortConfig {
  mode: string;
  showIndexes: boolean;
  allowUnsort: boolean;
}

export interface SmartGridSubHeader {
  [columnId: string]: string;
}

export interface SmartGridInfo {
  columns: SmartGridColumn[];
  actions: SmartGridAction[];
  controller: string;
  collection: string;
  pageable: false | SmartGridPageSize | { buttonCount: number; pageSize: number; pageSizes: number[] };
  page: number;
  sortable: boolean | object;
  sort: kendo.data.DataSourceSortItem;
  filterable: boolean | object;
  filter: kendo.data.DataSourceFilters;
  transaction: { [key: string]: string };
  selectedRows: string[];
  onlySelection: boolean;
  customData: unknown;
}

interface KendoVueGridRow extends Vue {
  dataItem?: SmartGridRowData;
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
    "kendo-grid-norecords": GridNoRecords,
    "kendo-grid-vue": Grid,
    "ank-action-menu": AnkActionMenu,
    "ank-export-button": AnkExportButton,
    "ank-expand-button": AnkGridExpandButton,
    "ank-grid-pager": AnkGridPager,
    "ank-columns-button": AnkGridColumnsButton
  },
  name: "ank-se-grid-vue"
})
export default class AnkSmartElementGrid extends Mixins(I18nMixin) {
  @Prop({
    default: "0",
    type: String
  })
  public collection: string;

  @Prop({
    type: Object
  })
  public customData!: object;

  @Prop({
    default: () => [],
    type: Array
  })
  public columns: SmartGridColumn[];
  @Prop({
    default: () => ({}),
    type: Object
  })
  public subHeader: SmartGridSubHeader;
  @Prop({
    default: () => [],
    type: Array
  })
  public actions: SmartGridAction[];
  @Prop({
    default: "DEFAULT_GRID_CONTROLLER",
    type: String
  })
  public controller: string;
  @Prop({
    default: " ",
    type: String
  })
  public actionColumnTitle!: string;
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
  public defaultExportButton: boolean;

  @Prop({
    default: false,
    type: Boolean
  })
  public defaultShownColumns: boolean;

  @Prop({
    default: false,
    type: Boolean
  })
  public defaultExpandable: boolean;
  @Prop({
    default: "-",
    type: String
  })
  public contextTitlesSeparator: string;

  @Prop({
    default: "",
    type: String
  })
  public emptyCellText;
  @Prop({
    default: "N/C",
    type: String
  })
  public inexistentCellText;
  @Prop({
    default: () => DEFAULT_SORT,
    type: [Boolean, Object]
  })
  public sortable: boolean | SmartGridSortConfig;

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
  public pageable: boolean | SmartGridPageSize;

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
  @Prop({
    default: "5rem",
    type: String
  })
  public maxRowHeight: string;

  public $refs: {
    smartGridWidget: Grid;
  };

  @Watch("$props", { deep: true })
  protected async onPropsChange(): Promise<void> {
    this.gridError = new GridError(this);
    this.$on("PageChange", this.onPageChange);

    try {
      await this._loadGridConfig();
      await this._loadGridContent();
      this.dataItems = this.dataItems.map(item => {
        return { ...item, selected: false };
      });
    } catch (error) {
      console.error(error);
    }
  }

  @Watch("isLoading", { immediate: true })
  protected onLoadingChange(newValue): void {
    kendo.ui.progress($(".smart-element-grid-widget", this.$el), !!newValue);
  }

  @Watch("dataItems")
  public watchDataItems(val): void {
    Vue.nextTick(() => {
      val.forEach(item => {
        if (item.selected) {
          this.$refs.smartGridWidget.$children.forEach((child: KendoVueGridRow) => {
            if (child.dataItem) {
              if (child.dataItem.properties.id === item.properties.id) {
                // @ts-ignore
                child.$el.firstElementChild.firstElementChild.checked = item.selected;
              }
            }
          });
        }
      });
    });
    this.$emit("DataBound", this.gridInstance);
  }
  public networkOnline = true;
  public transaction: { [key: string]: string } = null;
  public gridInstance: AnkSmartElementGrid = null;
  public gridError: GridError = null;
  public collectionProperties: { [key: string]: string } = {};
  public translations = {
    downloadAllResults: "Download all results",
    downloadReport: "Download",
    downloadSelection: "Download selected items",
    downloadAgain: "Retry",
    downloadCancel: "Cancel"
  };
  public onlySelection = false;
  public allColumns: SmartGridColumn[] = this.columns;
  public columnsList: SmartGridColumn[] = this.columns;
  public actionsList: SmartGridAction[] = this.actions;
  public dataItems: SmartGridRowData[] = [];
  public selectedRows: string[] = [];
  public isLoading = false;
  public currentSort: kendo.data.DataSourceSortItem = null;
  public currentFilter: {
    logic: string;
    filters: Array<{
      logic: string;
      field?: string;
      filters: Array<{ operator: string; field: string; value: string; displayValue?: string }>;
    }>;
  } = {
    logic: "and",
    filters: []
  };
  public currentPage: { total: number; skip: number; take: number } = {
    total: null,
    skip: 0,
    take:
      this.pageable && this.pageable !== true
        ? this.pageable.pageSize || DEFAULT_PAGER.pageSize
        : DEFAULT_PAGER.pageSize
  };
  public pager = this.pageable === true ? DEFAULT_PAGER : this.pageable;
  public sorter = this.sortable === true ? DEFAULT_SORT : this.sortable;

  public get gridInfo(): SmartGridInfo {
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
      onlySelection: this.onlySelection,
      customData: this.customData
    };
  }

  async created(): Promise<void> {
    window.addEventListener("online", this.updateOnlineStatus);
    window.addEventListener("offline", this.updateOnlineStatus);
    this.gridError = new GridError(this);
    this.$on("PageChange", this.onPageChange);

    try {
      await this._loadGridConfig();
      await this._loadGridContent();
      this.dataItems = this.dataItems.map(item => {
        return { ...item, selected: false };
      });
    } catch (error) {
      console.error(error);
    }
  }

  beforeDestroy(): void {
    this.$off("pageChange", this.onPageChange);
  }

  mounted(): void {
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
    this.$emit("GridReady");
  }
  public updateOnlineStatus(): Promise<void> {
    const condition = navigator.onLine;
    if (condition !== this.networkOnline && condition) {
      this.networkOnline = condition;
      return this._loadGridContent();
    } else {
      this.networkOnline = condition;
    }
  }

  public expandColumns(): void {
    $(this.$refs.smartGridWidget.$el).toggleClass("grid-row-collapsed");
  }

  public async onSettingsChange(changes): Promise<void> {
    if (changes) {
      Object.keys(changes).forEach(colId => {
        if (this.$refs.smartGridWidget) {
          if (changes[colId].display === true) {
            this.columns.map(function(column, index, tabColumns) {
              if (column.field === colId) {
                column.hidden = false;
              }
              return tabColumns;
            });
          } else if (changes[colId].display === false) {
            this.columns.map(function(column, index, tabColumns) {
              if (column.field === colId) {
                column.hidden = true;
              }
              return tabColumns;
            });
          }
        }
      });
    }
    await this._loadGridConfig();
  }

  protected async _loadGridConfig(): Promise<void> {
    this.isLoading = true;
    const url = this._getOperationUrl("config");
    const event = new GridEvent(
      {
        url: url
      },
      null,
      true // Cancelable
    );
    this.$emit("BeforeConfig", event);
    if (!event.isDefaultPrevented()) {
      this.$http
        .get(url, {
          params: this.gridInfo
        })
        .then(response => {
          this.columnsList = response.data.data.columns;
          this.allColumns = response.data.data.columns;
          this.columnsList = this.columnsList.filter(item => {
            if (item.hidden === undefined || item.hidden === false) {
              return item;
            }
          });
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
              title: this.actionColumnTitle,
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
          this.$emit("AfterConfig", responseEvent);
          this.isLoading = false;
        })
        .catch(error => {
          console.error(error);
          this.isLoading = false;
        });
    } else {
      this.isLoading = false;
    }
  }

  protected async _loadGridContent(): Promise<void> {
    this.isLoading = true;
    const url = this._getOperationUrl("content");
    const event = new GridEvent(
      {
        url: url,
        queryParams: this.gridInfo
      },
      null,
      true // Cancelable
    );
    this.$emit("BeforeContent", event);
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
            item.selected = this.selectedRows.indexOf(item.properties.id as string) !== -1;
          });
          const responseEvent = new GridEvent(
            {
              content: response.data.data
            },
            null,
            false
          );
          this.$emit("AfterContent", responseEvent);
          this.isLoading = false;
        })
        .catch(error => {
          console.error(error);
          this.isLoading = false;
        });
    } else {
      this.isLoading = false;
    }
  }

  protected onSelectionChange(event): void {
    this.dataItems.find(item => {
      if (item.properties.id === event.dataItem.properties.id) {
        const checkedValue = event.event.target.checked;
        item.selected = checkedValue;
        if (checkedValue && this.selectedRows.indexOf(item.properties.id as string) === -1) {
          this.selectedRows.push(event.dataItem.properties.id);
        } else {
          this.selectedRows.splice(this.selectedRows.indexOf(item.properties.id as string), 1);
        }
      }
    });
    this._loadGridContent();
  }

  protected cellRenderFunction(createElement, tdElement: VNode, props, listeners): VNode | VNode[] {
    const columnConfig = this.columnsList[props.columnIndex];
    const event = new GridEvent(
      {
        rowData: props.dataItem,
        columnConfig: columnConfig
      },
      null,
      false,
      "GridCellRenderEvent"
    );
    this.$emit("BeforeGridCellRender", event);
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
            RowActionClick: (...args) => this.$emit("RowActionClick", ...args)
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
        const options = {
          props: {
            ...props,
            columnConfig,
            gridComponent: this
          },
          scopedSlots: {}
        };
        if (this.$scopedSlots && this.$scopedSlots.emptyCell) {
          // @ts-ignore
          options.scopedSlots.emptyCell = props =>
            this.$scopedSlots.emptyCell({ renderElement, props, listeners, columnConfig });
        }
        if (this.$scopedSlots && this.$scopedSlots.inexistentCell) {
          // @ts-ignore
          options.scopedSlots.inexistentCell = props =>
            this.$scopedSlots.inexistentCell({ renderElement, props, listeners, columnConfig });
        }
        renderElement = createElement(AnkGridCell, options);
      }
      if (this.$scopedSlots && this.$scopedSlots.cellTemplate) {
        const scopeResult = this.$scopedSlots.cellTemplate({
          renderElement,
          props,
          listeners,
          columnConfig
        });
        if (scopeResult) {
          return scopeResult;
        } else {
          return renderElement;
        }
      }
    }
    return renderElement;
  }

  protected headerCellRenderFunction(createElement, defaultRendering, props): VNode {
    const columnConfig = this.columnsList.find(
      (c: kendo.data.DataSourceFilter & { field?: string }) => c.field === props.field
    );
    return createElement(AnkGridHeaderCell, {
      props: { ...props, columnConfig, grid: this },
      on: {
        SortChange: this.onSortChange,
        FilterChange: this.onFilterChange
      }
    });
  }

  protected subHeaderCellRenderFunction(createElement, defaultRendering, props): VNode {
    const columnConfig = this.columnsList.find(
      (c: kendo.data.DataSourceFilter & { field?: string }) => c.field === props.field
    );
    const options = {
      props: {
        ...props,
        columnConfig,
        gridComponent: this,
        tag: "div"
      },
      class: `smart-element-grid-column-footer smart-element-grid-column-footer--${columnConfig.field}`
    };
    if (this.subHeader && this.subHeader[columnConfig.field]) {
      if (columnConfig.property) {
        options.props.fieldValue = this.subHeader[columnConfig.field];
      } else {
        options.props.fieldValue = {
          displayValue: this.subHeader[columnConfig.field],
          value: this.subHeader[columnConfig.field]
        };
      }
    } else {
      if (columnConfig.property) {
        options.props.fieldValue = null;
      } else {
        options.props.fieldValue = {
          displayValue: null,
          value: null
        };
      }
    }
    return createElement(AnkGridCell, options);
  }

  protected _getOperationUrl(operation): string {
    return CONTROLLER_URL.replace(/\{(\w+)\}/g, (match, substr) => {
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

  protected async onSortChange(sortEvt): Promise<void> {
    this.currentSort = sortEvt.sort;
    await this._loadGridContent();
  }

  protected async onPageChange(pagerEvt): Promise<void> {
    if (this.networkOnline) {
      this.currentPage = Object.assign({}, this.currentPage, pagerEvt.page);
      this.pager = Object.assign({}, this.pager, { pageSize: pagerEvt.page.take });
    }
    await this._loadGridContent();
  }

  protected async onFilterChange(filterEvt): Promise<void> {
    if (this.networkOnline) {
      if (filterEvt) {
        const filters = this.currentFilter.filters.filter(
          (f: kendo.data.DataSourceFilter & { field?: string }) => f.field !== filterEvt.field
        );
        if (filterEvt.filters) {
          filters.push(filterEvt);
        }
        this.currentFilter.filters = filters;
      }
    }
    await this._loadGridContent();
  }

  protected onColumnReorder(reorderEvt): void {
    this.columnsList = reorderEvt.columns;
  }

  protected export(
    exportAll = true,
    directDownload = true,
    onPolling: (...args: unknown[]) => void = (): void => {},
    pollingTime = 500,
    onExport = this.doDefaultExport.bind(this)
  ): Promise<any> {
    if (this.networkOnline) {
      const beforeEvent = this.sendBeforeExportEvent(onExport, onPolling);
      if (!beforeEvent.isDefaultPrevented()) {
        const exportCb = beforeEvent.onExport;
        const pollingCb = beforeEvent.onPolling;
        if (typeof onExport === "function") {
          this.sendBeforePollingEvent();
          const promise = this.createExportTransaction()
            .then(() => {
              return this.doTransactionExport(
                this.transaction,
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
      } else {
        this.gridError.error("Export failed: network disconnected");
        this.sendErrorEvent("Export failed: network disconnected");
      }
    }
  }

  protected async doDefaultExport(): Promise<any> {
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

  protected downloadExportFile(blobFile): void {
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

  protected sendBeforeExportEvent(onExport, onPolling): GridExportEvent {
    const event = new GridExportEvent({
      component: this,
      type: "export"
    });
    event.onExport = onExport;
    event.onPolling = onPolling;
    this.$emit("BeforeGridExport", event);
    return event;
  }

  protected sendBeforePollingEvent(): GridExportEvent {
    const event = new GridExportEvent(null, null, false);
    this.$emit("BeforePollingGridExport", event);
    return event;
  }

  protected sendErrorEvent(message): GridExportEvent {
    const event = new GridExportEvent(
      {
        message: message
      },
      null,
      false
    );
    this.$emit("ExportError", event);
    return event;
  }

  protected async createExportTransaction(): Promise<void> {
    const exportUrl = this._getOperationUrl("export");
    await this.$http
      .get(exportUrl)
      .then(response => {
        this.transaction = response.data;
        return true;
      })
      .catch(err => {
        this.gridError.error(err);
        this.sendErrorEvent(err);
      });
  }

  protected doTransactionExport(
    transaction,
    queryParams,
    exportRequest,
    pollingRequest,
    pollingTime,
    directDownload
  ): { data: string } {
    const transactionId = transaction.transactionId;
    const file = exportRequest(transaction, queryParams, directDownload);
    this.pollTransaction(transactionId, pollingRequest, pollingTime);

    return file;
  }

  protected pollTransaction(transactionId, pollingCb, pollingTime): void {
    let timer = null;
    const getStatus = (): void => {
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
