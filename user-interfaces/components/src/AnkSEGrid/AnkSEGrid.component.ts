import { Component, Mixins, Prop, Vue, Watch } from "vue-property-decorator";
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
import GridError, { GridErrorCodes } from "./utils/GridError";
import GridExportEvent from "./AnkGridEvent/AnkGridExportEvent";
import I18nMixin from "../../mixins/AnkVueComponentMixin/I18nMixin";

import $ from "jquery";
import ISmartFilter from "../AnkSmartCriteria/Types/ISmartFilter";

const CONTROLLER_URL = "/api/v2/grid/controllers/{controller}/{op}/{collection}";

const needWatchUpdate = (newValue, oldValue) => {
  // need an update if it's not the same type
  if (typeof newValue !== typeof oldValue) {
    return true;
  }

  // Quick case for array
  if (Array.isArray(newValue)) {
    if (newValue.length !== oldValue.length) {
      return true;
    }
  }

  // parameters have the same type
  if (newValue !== null && (typeof newValue === "object" || Array.isArray(newValue))) {
    let result = false;
    // Compare if values of newValue object are contain in oldValue object
    Object.keys(newValue).forEach(key => {
      result = result || needWatchUpdate(newValue[key], oldValue[key]);
    });
    return result;
  } else {
    // simple types case
    return newValue !== oldValue;
  }
};

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
  filterable?: boolean | SmartGridFilterable;
  transaction?: boolean | object;
  resizable?: boolean;
  orderIndex?: number;
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
}

export interface SmartGridPageSize {
  page: number;
  pageSize: number;
  total: number;
}

export interface SmartGridPageable {
  buttonCount: number;
  info: boolean;
  showCurrentPage: boolean;
  pageSize: "ALL" | number;
  pageSizes: number[];
}

export interface SmartGridSubHeader {
  [columnId: string]: string;
}

export interface SmartGridFilter {
  logic?: string;
  filters?: SmartGridFilter[];
  field?: string;
  operator?: string;
  value?: string | number;
}

export interface SmartGridInfo {
  columns: SmartGridColumn[];
  actions: SmartGridAction[];
  controller: string;
  collection: string;
  pageable: false | SmartGridPageable;
  page: number;
  sortable: boolean | object;
  sort: kendo.data.DataSourceSortItem[];
  filterable: boolean | SmartGridFilterable;
  filter: SmartGridFilter;
  transaction: { [key: string]: string };
  selectedRows: string[];
  onlySelection: boolean;
  customData: unknown;
  configUrl: string;
  contentUrl: string;
  exportUrl: string;
}

export enum SmartGridFilterOperator {
  EQUAL = "eq",
  NOT_EQUAL = "neq",
  CONTAINS = "contains",
  TITLE_CONTAINS = "title_contains",
  STARTS_WITH = "startswith",
  DOES_NOT_CONTAIN = "doesnotcontain",
  IS_EMPTY = "isempty",
  IS_NOT_EMPTY = "isnotempty"
}

export interface SmartGridSortable {
  allowUnsort?: boolean;
  showIndexes?: boolean;
  mode?: string;
}

export interface SmartGridFilterable {
  [columnId: string]: {
    autocomplete?: { url?: string; outputs?: object; inputs?: object };
    activeOperators?: string[];
    singleFilter?: boolean;
  };
}

const DEFAULT_FILTERABLE = true;

const DEFAULT_PAGER: SmartGridPageable = {
  buttonCount: 0,
  showCurrentPage: false,
  info: true,
  pageSize: 10,
  pageSizes: [10, 20, 50]
};

const DEFAULT_SORT = {
  mode: "multiple",
  showIndexes: true,
  allowUnsort: true
};

function computeSkipFromPage(page, pageSize): number {
  return pageSize !== "ALL" ? (page - 1) * pageSize : 0;
}

function computePage(currentPage, pageable): number {
  return pageable.pageSize && pageable.pageSize !== "ALL"
    ? (currentPage.skip + currentPage.take) / currentPage.take
    : 1;
}

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
  /* Save columns options deactivated for enhancement issue #862

    @Prop({
    default: "",
    type: String
    })
    public persistStateKey: string;
  */
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
  public sortable: boolean | SmartGridSortable;

  @Prop({
    default: () => DEFAULT_FILTERABLE,
    type: [Boolean, Object]
  })
  public filterable: boolean | SmartGridFilterable;
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
  public pageable: boolean | SmartGridPageable;

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

  @Prop({
    default: CONTROLLER_URL,
    type: String
  })
  public contentUrl: string;

  @Prop({
    default: CONTROLLER_URL,
    type: String
  })
  public configUrl: string;

  @Prop({
    default: CONTROLLER_URL,
    type: String
  })
  public exportUrl: string;
  @Prop({
    default: "ank-smart-grid-selected",
    type: String
  })
  public selectedField: string;
  @Prop({
    default: "smart_element_grid_action_menu",
    type: String
  })
  public actionField: string;
  @Prop({
    default: () => [],
    type: Array
  })
  public sort!: kendo.data.DataSourceSortItem[];
  @Prop({
    default: () => ({ logic: "and", filters: [] }),
    type: Object
  })
  public filter!: SmartGridFilter;
  @Prop({
    default: 1,
    type: Number
  })
  public page!: number;
  @Prop({
    default: true,
    type: Boolean
  })
  public autoFit!: boolean;

  @Prop({
    default: () => ({ logic: "and", filters: [] }),
    type: Object
  })
  public smartCriteriaValue!: ISmartFilter;

  public $refs: {
    smartGridWidget: Grid;
  };

  //region watchProps
  @Watch("sort", { deep: true })
  public async watchSort(newValue): Promise<void> {
    if (needWatchUpdate(newValue, this.currentSort)) {
      this.currentSort = newValue;
      return await this.refreshGrid();
    }
  }
  @Watch("pageable", { deep: true })
  public async watchPageable(newValue): Promise<void> {
    if (!newValue) {
      this.pager = false;
    } else {
      let toCompare = newValue;
      if (newValue === true) {
        toCompare = DEFAULT_PAGER;
      }
      if (needWatchUpdate(toCompare, this.pager)) {
        this.pager = toCompare;
        return await this.refreshGrid();
      }
    }
  }

  @Watch("sortable", { deep: true })
  public async watchSortable(newValue): Promise<void> {
    if (needWatchUpdate(newValue, this.sorter)) {
      this.sorter = newValue === true ? DEFAULT_SORT : newValue;
      return await this.refreshGrid();
    }
  }
  @Watch("page", { deep: true })
  public async watchPage(newValue): Promise<void> {
    const skip = computeSkipFromPage(newValue, this.currentPage.take);
    if (this.currentPage.skip !== skip) {
      this.currentPage.skip = skip;
      return await this.refreshGrid();
    }
  }

  @Watch("filter", { deep: true })
  public async watchFilter(newValue): Promise<void> {
    if (this.currentFilter !== newValue) {
      this.currentFilter = newValue;
      return await this.refreshGrid();
    }
  }

  @Watch("collection")
  protected async oncollectionChange(): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("customData")
  protected async oncustomDataChange(): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("columns")
  protected async oncolumnsChange(): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("subHeader")
  protected async onsubHeaderChange(): Promise<void> {
    return await this.refreshGrid();
  }
  /* Save columns options deactivated for enhancement issue #862
    @Watch("persistStateKey")
    protected async onpersistStateKeyChange(newValue, oldValue): Promise<void> {
      return await this.refreshGrid();
    }
  */

  @Watch("filterable")
  protected async onfilterableChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("reorderable")
  protected async onreorderableChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("resizable")
  protected async onresizableChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }
  @Watch("selectable")
  protected async onselectableChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }
  @Watch("checkable")
  protected async oncheckableChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("persistSelection")
  protected async onpersistSelectionChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }
  @Watch("maxRowHeight")
  protected async onmaxRowHeightChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("contentUrl")
  protected async oncontentUrlChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("configUrl")
  protected async onconfigUrlChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("exportUrl")
  protected async onexportUrlChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }
  @Watch("selectedField")
  protected async onselectedFieldChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }

  @Watch("autoFit")
  protected async onautoFitChange(newValue, oldValue): Promise<void> {
    return await this.refreshGrid();
  }
  //endregion watchProps

  @Watch("isLoading", { immediate: true })
  protected onLoadingChange(newValue): void {
    kendo.ui.progress($(".smart-element-grid-widget", this.$el), !!newValue);
  }

  @Watch("dataItems")
  public watchDataItems(): void {
    this.$emit("dataBound", this.gridInstance);
  }

  @Watch("smartCriteriaValue")
  public async watchSearchCriteriaValue(newValue): Promise<void> {
    const filtered = this.currentFilter.filters.filter(filter => filter.operator !== "searchCriteria");
    filtered.push({
      field: "searchCriteriaField",
      operator: "searchCriteria",
      value: newValue
    });
    this.currentFilter.filters = filtered;
    return await this._loadGridContent();
  }

  @Watch("selectedRows", { deep: true })
  protected onSelectedRowChange(newValue): void {
    const gridEvent = new GridEvent(
      {
        selectedRows: newValue
      },
      null,
      false,
      "GridSelectionChangeEvent"
    );
    this.$emit("selectionChange", gridEvent);
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
  public columnsList: SmartGridColumn[] = this.columns;
  public actionsList: SmartGridAction[] = this.actions;
  public dataItems: SmartGridRowData[] = [];
  public selectedRows: string[] = [];
  public isLoading = false;
  public currentSort: kendo.data.DataSourceSortItem[] = this.sort;
  public currentFilter: SmartGridFilter = this.filter;
  public currentPage: { total: number; skip: number; take: "ALL" | number } = {
    total: null,
    skip: computeSkipFromPage(
      this.page,
      this.pageable && this.pageable !== true
        ? this.pageable.pageSize || DEFAULT_PAGER.pageSize
        : DEFAULT_PAGER.pageSize
    ),
    take:
      this.pageable && this.pageable !== true
        ? this.pageable.pageSize === "ALL"
          ? 0
          : this.pageable.pageSize
        : DEFAULT_PAGER.pageSize
  };
  public pager = this.pageable === true ? DEFAULT_PAGER : this.pageable;
  public sorter: false | SmartGridSortable = this.sortable === true ? DEFAULT_SORT : this.sortable;

  public get gridInfo(): SmartGridInfo {
    return {
      columns: this.columns,
      actions: this.actions,
      controller: this.controller,
      collection: this.collection,
      pageable: this.pager,
      page: computePage(this.currentPage, this.pager),
      sortable: this.sorter,
      sort: this.currentSort,
      filterable: this.filterable,
      filter: this.currentFilter,
      transaction: this.transaction,
      selectedRows: this.selectedRows,
      onlySelection: this.onlySelection,
      customData: this.customData,
      contentUrl: this._getOperationUrl("content"),
      configUrl: this._getOperationUrl("config"),
      exportUrl: this._getOperationUrl("export")
    };
  }

  async created(): Promise<void> {
    this.gridInstance = this;
    window.addEventListener("online", this.updateOnlineStatus);
    window.addEventListener("offline", this.updateOnlineStatus);
    this.gridError = new GridError(this);
    this.$on("pageChange", this.onPageChange);

    return await this.refreshGrid();
  }

  beforeDestroy(): void {
    this.$off("pageChange", this.onPageChange);
  }

  mounted(): void {
    /* Save columns options deactivated for enhancement issue #862

    let saveColumnsOptions = null;
    if (this.persistStateKey) {
      if (window && window.localStorage) {
        saveColumnsOptions = localStorage.getItem(this.persistStateKey);
        if (saveColumnsOptions) {
          saveColumnsOptions = JSON.parse(saveColumnsOptions);
        }
      } else {
        this.gridError.error(
          "Persistent grid state is disabled, local storage is not supported by the current environment",
          GridErrorCodes.LOCAL_STORAGE
        );
      }
    }
     */
    this.$emit("gridReady");
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

  public async refreshGrid(onlyContent = false): Promise<void> {
    try {
      if (!onlyContent) {
        await this._loadGridConfig();
        this.selectedRows = [];
      }
      await this._loadGridContent();
    } catch (error) {
      this.gridError.error(error);
    }
  }

  public async addSort(...sortItem: kendo.data.DataSourceSortItem[]): Promise<void> {
    let sort = [...sortItem];
    if (this.sorter) {
      if (this.sorter.mode !== "multiple") {
        sort = [sortItem[0]];
      } else if (this.currentSort) {
        sort = this.currentSort
          .filter(s => {
            const alreadyExist = sort.find(newSort => newSort.field === s.field);
            return !alreadyExist;
          })
          .concat(sort);
      }
      this.currentSort = sort;
      return await this._loadGridContent();
    }
  }

  public async addFilter(...filterItem: SmartGridFilter[]): Promise<void> {
    filterItem.forEach(filter => {
      this.currentFilter.filters.push(filter);
    });
    return await this._loadGridContent();
  }

  public expandColumns(): void {
    $(this.$refs.smartGridWidget.$el).toggleClass("grid-row-collapsed");
  }

  public onSettingsChange(changes): void {
    if (changes) {
      Object.keys(changes).forEach(colId => {
        const column = this.columnsList.find(c => c.field === colId);
        if (column) {
          this.$set(column, "hidden", !changes[colId].display);
        }
      });
    }
  }

  protected get rowsData(): SmartGridRowData[] {
    return this.dataItems.map(item => {
      if (this.selectable || this.checkable) {
        return {
          ...item,
          [this.selectedField]: this.selectedRows.indexOf(item.properties.id.toString()) !== -1
        };
      }
      return item;
    });
  }

  protected get allColumns(): SmartGridColumn[] {
    const columns = [];
    // Prepend checkable column if needed
    if (this.checkable) {
      columns.push({
        field: this.selectedField,
        filterable: false,
        orderIndex: 0,
        title: " ",
        width: 35,
        headerAttributes: {
          class: "checkable-grid-header grid-cell-align-center toggle-all-rows",
          "data-id": "ank-se-grid-checkable"
        }
      });
    }

    // Add data columns
    columns.push(...this.columnsList);

    // Add actions column if needed
    if (this.actionsList.length > 0) {
      let menuWidth = 120;
      if (this.actionsList.length === 2) {
        menuWidth = (this.actionsList[0].title.length + this.actionsList[1].title.length) * 10;
      }
      columns.push({
        // @ts-ignore
        width: menuWidth,
        orderIndex: columns.length,
        field: this.actionField,
        title: this.actionColumnTitle,
        abstract: true,
        withContext: false,
        filterable: false,
        sortable: false,
        resizable: false
      });
    }

    return columns;
  }

  protected async _loadGridConfig(): Promise<void> {
    this.isLoading = true;
    const url = this._getOperationUrl("config");
    const event = new GridEvent(
      {
        url: url,
        queryParams: this.gridInfo
      },
      null,
      true // Cancelable
    );
    this.$emit("beforeConfig", event);
    if (!event.isDefaultPrevented()) {
      this.$http
        .get(event.data.url, {
          params: event.data.queryParams
        })
        .then(response => {
          this.collectionProperties = response.data.data.collection || {};
          this.columnsList = response.data.data.columns;
          if (response.data.data.pageable === true) {
            this.pager = DEFAULT_PAGER;
          } else if (response.data.data.pageable === false) {
            this.pager = false;
          } else {
            this.pager = Object.assign({}, DEFAULT_PAGER, response.data.data.pageable);
          }
          if (response.data.data.actions.length > 0) {
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
          this.isLoading = false;
        })
        .catch(error => {
          if (error && error.response && error.response.status === 404) {
            this.gridError.error("The configuration URL '" + url + "' does not exist", GridErrorCodes.URL_NOT_EXIST);
          } else {
            this.gridError.error(error, GridErrorCodes.CONFIGURATION);
          }
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
    this.$emit("beforeContent", event);
    if (!event.isDefaultPrevented()) {
      this.$http
        .get(event.data.url, {
          params: event.data.queryParams
        })
        .then(response => {
          const pager = response.data.data.requestParameters.pager;
          this.currentPage.total = pager.total;
          this.currentPage.skip = pager.skip;
          this.currentPage.take = pager.take;
          this.dataItems = response.data.data.content;
          const responseEvent = new GridEvent(
            {
              content: response.data.data
            },
            null,
            false
          );
          this.$emit("afterContent", responseEvent);
          this.isLoading = false;
        })
        .catch(error => {
          if (error && error.response && error.response.status === 404) {
            this.gridError.error("The content URL '" + url + "' does not exist", GridErrorCodes.URL_NOT_EXIST);
          } else {
            this.gridError.error(error, GridErrorCodes.CONTENT);
          }
          this.isLoading = false;
        });
    } else {
      this.isLoading = false;
    }
  }

  protected onSelectionChange(event): void {
    if (this.checkable) {
      if (event.event.target.checked) {
        this.selectedRows.push(event.dataItem.properties.id.toString());
      } else {
        this.selectedRows.splice(this.selectedRows.indexOf(event.dataItem.properties.id.toString()), 1);
      }
    }
  }

  protected cellRenderFunction(createElement, tdElement: VNode, props, listeners): VNode | VNode[] {
    const columnConfig = this.allColumns.find(c => c.field === props.field);
    const event = new GridEvent(
      {
        rowData: props.dataItem,
        columnConfig: columnConfig
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
            rowActionClick: (...args): void => {
              this.$emit("rowActionClick", ...args);
            }
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
    } else if (props.field === this.selectedField) {
      return renderElement;
    } else {
      if (columnConfig) {
        const options: any = {
          props: {
            ...props,
            columnConfig,
            gridComponent: this
          },
          scopedSlots: {},
          on: {
            itemClick: (): void => this.onRowClick({ dataItem: props.dataItem })
          }
        };
        if (this.$scopedSlots && this.$scopedSlots.emptyCell) {
          options.scopedSlots.emptyCell = (props): VNode[] =>
            this.$scopedSlots.emptyCell({ renderElement, props, listeners, columnConfig });
        }
        if (this.$scopedSlots && this.$scopedSlots.inexistentCell) {
          options.scopedSlots.inexistentCell = (props): VNode[] =>
            this.$scopedSlots.inexistentCell({ renderElement, props, listeners, columnConfig });
        }
        renderElement = createElement(AnkGridCell, options);
      }
      if (this.$scopedSlots && this.$scopedSlots.cellTemplate) {
        const scopeResult = this.$scopedSlots.cellTemplate({
          renderElement,
          props,
          listeners: {
            ...listeners,
            ItemClick: (): void => this.onRowClick({ dataItem: props.dataItem })
          },
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

  protected headerCellRenderFunction(createElement, defaultRendering, props): VNode | VNode[] {
    const columnConfig = this.allColumns.find(c => c.field === props.field);

    const renderElement = createElement(AnkGridHeaderCell, {
      props: { ...props, columnConfig, grid: this },
      on: {
        sortChange: this.onSortChange,
        filterChange: this.onFilterChange
      },
      key: props.field
    });

    if (this.$scopedSlots && this.$scopedSlots.headerTemplate) {
      const scopeResult = this.$scopedSlots.headerTemplate({
        renderElement,
        props,
        columnConfig,
        grid: this
      });
      if (scopeResult) {
        return scopeResult;
      } else {
        return renderElement;
      }
    }
    return renderElement;
  }

  protected subHeaderCellRenderFunction(createElement, defaultRendering, props): VNode {
    const columnConfig = this.allColumns.find(c => c.field === props.field);
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
    let url = CONTROLLER_URL;
    switch (operation) {
      case "config":
        url = this.configUrl;
        break;
      case "content":
        url = this.contentUrl;
        break;
      case "export":
        url = this.exportUrl;
        break;
    }
    return url.replace(/\{(\w+)\}/g, (match, substr) => {
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
    this.addSort(...sortEvt.sort);
  }

  protected async onPageChange(pagerEvt): Promise<void> {
    if (this.networkOnline) {
      this.currentPage = Object.assign({}, this.currentPage, pagerEvt.data.page);
      this.pager = Object.assign({}, this.pager, { pageSize: pagerEvt.data.page.take });
    }
    return await this._loadGridContent();
  }

  protected async onFilterChange(filterEvt): Promise<void> {
    if (this.networkOnline) {
      if (filterEvt) {
        const filters = this.currentFilter.filters.filter((f: SmartGridFilter) => f.field !== filterEvt.field);
        if (filterEvt.filters) {
          filters.push(filterEvt);
        }
        this.currentFilter.filters = filters;
      }
    }
    return await this._loadGridContent();
  }

  protected onColumnReorder(reorderEvt): void {
    this.columnsList = this.columnsList.map(c => {
      const columnReorder = reorderEvt.columns.find(col => col.field === c.field);
      c.orderIndex = columnReorder.orderIndex;
      return c;
    });
  }

  protected export(
    exportAll = true,
    directDownload = true,
    onPolling: (...args: unknown[]) => void = (): void => {},
    pollingTime = 500,
    onExport = this.doDefaultExport.bind(this)
  ): Promise<boolean | string | void> {
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

  protected async doDefaultExport(): Promise<void> {
    const exportUrl = this._getOperationUrl("export");
    await this.$http
      .get(exportUrl, {
        params: this.gridInfo,
        responseType: "blob"
      })
      .then(response => this.downloadExportFile(response.data))
      .catch(error => {
        if (error && error.response && error.response.status === 404) {
          this.gridError.error("The export URL '" + exportUrl + "' does not exist", GridErrorCodes.URL_NOT_EXIST);
        } else {
          this.gridError.error(error, GridErrorCodes.EXPORT);
        }
        this.sendErrorEvent(error);
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
    this.$emit("beforeGridExport", event);
    return event;
  }

  protected sendBeforePollingEvent(): GridExportEvent {
    const event = new GridExportEvent(null, null, false);
    this.$emit("beforePollingGridExport", event);
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
    this.$emit("exportError", event);
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
      .catch(error => {
        if (error && error.response && error.response.status === 404) {
          this.gridError.error("The export URL '" + exportUrl + "' does not exist", GridErrorCodes.URL_NOT_EXIST);
        } else {
          this.gridError.error(error, GridErrorCodes.EXPORT);
        }
        this.sendErrorEvent(error);
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
          this.gridError.error(err, GridErrorCodes.EXPORT_POLLING);
          if (timer) {
            clearTimeout(timer);
          }
        });
    };
    getStatus();
  }

  protected onRowClick(event): void {
    if (!this.checkable && this.selectable && event.dataItem && event.dataItem.properties.id) {
      this.$set(this.selectedRows, 0, event.dataItem.properties.id.toString());
    }
    const gridEvent = new GridEvent(
      {
        dataItem: event.dataItem
      },
      null,
      false,
      "GridRowClickEvent"
    );
    this.$emit("rowClick", gridEvent);
  }
}
