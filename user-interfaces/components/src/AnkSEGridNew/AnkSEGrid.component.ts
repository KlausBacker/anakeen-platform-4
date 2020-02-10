import { Component, Prop, Vue } from "vue-property-decorator";
import AnkProgress from "./AnkProgress/AnkProgress.vue";
import AnkActionMenu from "./AnkActionMenu/AnkActionMenu.vue";
import AnkExportButton from "./AnkExportButton/AnkExportButton.vue";
import { Grid } from "@progress/kendo-vue-grid";
import { VNode } from "vue/types/umd";
import GridActions from "../AnkSEGrid/utils/GridActions";
import GridEvent from "../AnkSEGrid/utils/GridEvent";
import GridError from "../AnkSEGrid/utils/GridError";

const CONTROLLER_URL = "/api/v2/grid/controllers/{controller}/{op}/{collection}";

interface SmartGridColumn {
  field: string;
  smartType: string;
  title: string;
  abstract: boolean;
  property: boolean;
  encoded: boolean;
  hidden: boolean;
  sortable: boolean;
  filterable: boolean | object;
  transaction: boolean | object;
}

interface SmartGridActions {
  action: string,
  title: string,
  iconClass: string
}

const DEFAULT_PAGER = {
  buttonCount: 0,
  pageSize: 10,
  pageSizes: [10, 20, 50]
};

const DEFAULT_SORT = {
  mode: "multiple",
  showIndexes: true,
  allowUnsort: false
};

@Component({
  components: {
    "kendo-grid-vue": Grid,
    "ank-progress": AnkProgress,
    "ank-action-menu": AnkActionMenu,
    "ank-export-button": AnkExportButton
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
    default: () => [],
    type: Array
  })
  public actions: SmartGridActions[];
  @Prop({
    default: "",
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
    default: "menu",
    type: String
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
  public columnsList: any = this.columns;
  public actionsList: any = this.actions;
  public dataItems: any = [];
  public isLoading: boolean = false;
  public currentSort: any = null;
  public currentPage: any = {
    total: null,
    skip: 0,
    take:
      this.pageable && this.pageable !== true
        ? this.pageable.pageSize || DEFAULT_PAGER.pageSize
        : DEFAULT_PAGER.pageSize
  };
  public pager: any = this.pageable === true ? DEFAULT_PAGER : this.pageable;

  public get gridInfo() {
    return {
      columns: this.columns,
      actions: this.actions,
      controller: this.controller,
      collection: this.collection,
      pageable: this.pager,
      page: (this.currentPage.skip + this.currentPage.take) / this.currentPage.take,
      sortable: this.sortable,
      sort: this.currentSort,
      filterable: this.filterable,
      transaction: this.transaction
    };
  }

  async created() {
    this.isLoading = true;
    this.gridActions = new GridActions(this);
    this.gridError = new GridError(this);

    try {
      await this._loadGridConfig();
      await this._loadGridContent();
      this.isLoading = false;
    } catch (error) {
      console.error(error);
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
  }

  protected async _loadGridConfig() {
    const url = this._getOperationUrl("config");
    const response = await this.$http.get(url, {
      params: this.gridInfo
    });
    this.columnsList = response.data.data.columns;
    if (response.data.data.pageable === true) {
      this.pager = DEFAULT_PAGER;
    } else if (response.data.data.pageable === false) {
      this.pager = false;
    } else {
      this.pager = Object.assign({}, DEFAULT_PAGER, response.data.data.pageable);
    }
    if (response.data.data.actions.length > 0) {
      this.columnsList.push({
        field: "actionMenu",
        abstract: true,
        withContext: true,
        context: ["Custom"],
        sortable: false
      });
      this.actionsList = this.formatActionMenu(response.data.data.actions);
    }
  }

  protected async _loadGridContent() {
    const url = this._getOperationUrl("content");
    const response = await this.$http.get(url, {
      params: this.gridInfo
    });
    const pager = response.data.data.requestParameters.pager;
    this.currentPage.total = pager.total;
    this.currentPage.skip = pager.skip;
    this.currentPage.take = pager.take;
    this.dataItems = response.data.data.content;
    console.log(this.dataItems);
    this.$emit("grid-data-bound", this.gridInstance);
  }

  protected cellRenderFunction(createElement, tdElement: VNode, props, listeners) {
    const columnConfig = this.columnsList[props.columnIndex];
    if (props.field === "actionMenu") {
      if (this.actionsList.length > 0) {
        return createElement(
          AnkActionMenu,
          {
            props: {
              actions: this.actionsList,
              gridComponent: this
            }
          },
          props.dataItem.actionMenu
        );
      }
    } else {
      if (columnConfig.property) {
        return createElement("td", props.dataItem.properties[columnConfig.field]);
      } else if (columnConfig.abstract) {
        return createElement("td", props.dataItem.abstract[columnConfig.field].displayValue);
      } else {
        return createElement("td", Object.assign({}, props.dataItem.attributes[columnConfig.field]).displayValue);
      }
    }
    return tdElement;
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

  protected formatActionMenu(actions) {
    const actionsColumn = [];
    const allActionsConfig: any = actions;
    const subCommands = [];
    allActionsConfig.forEach((config, index, selfArray) => {
      if (selfArray.length <= 2 || index < 1) {
        actionsColumn.push({
          name: config.action,
          text: config.title,
          iconClass: config.iconClass
        });
      } else {
        subCommands.push({
          name: config.action,
          text: config.title,
          iconClass: config.iconClass
        });
      }
    });
    if (subCommands.length) {
      actionsColumn.push({
        name: "_subcommands",
        text: "",
        iconClass: "k-icon k-i-more-vertical",
        subActions: subCommands
      });
    }
    return actionsColumn;
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

            return this.doTransactionExport(transaction, this.gridInfo, exportCb, pollingCb, pollingTime, directDownload);
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
    await this.$http.get(exportUrl, {
      params: this.gridInfo,
      responseType: "blob"
    }).then(response => this.downloadExportFile(response.data))
          .catch(err => {
            this.gridError.error(err);
            this.sendErrorEvent(err);

          });
  }

  protected downloadExportFile(blobFile) {
    const blob = new Blob([blobFile], {type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
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
    this.$emit("before-grid-export", event);
    return event;
  }

  protected sendBeforePollingEvent() {
    const event = new GridEvent(null, null, false);
    this.$emit("before-polling-grid-export", event);
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
