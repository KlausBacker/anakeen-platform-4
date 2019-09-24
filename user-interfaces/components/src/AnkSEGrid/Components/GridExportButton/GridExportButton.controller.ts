import "@progress/kendo-ui/js/kendo.menu";
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";
import GridEvent from "../../utils/GridEvent";
@Component({
  name: "ank-se-grid-export-button"
})
export default class GridExportButtonController extends Vue {
  private static displayExportPendingStatus(exportElement: any, indeterminate = false) {
    const menu = exportElement;
    menu.find("ul.grid-export-action-menu").css("display", "none");
    menu.find(".grid-export-status--error").css("display", "none");
    const returnDom = menu.find(".grid-export-status--pending");
    if (indeterminate) {
      returnDom
        .find(".grid-export-status-progress-bar-wrapper")
        .addClass("grid-export-status-progress-bar-wrapper--indeterminate");
      returnDom.find(".grid-export-status-progress-bar").css("width", "40");
    } else {
      returnDom
        .find(".grid-export-status-progress-bar-wrapper")
        .removeClass("grid-export-status-progress-bar-wrapper--indeterminate");
      returnDom.find(".grid-export-status-progress-bar").css("width", "0");
    }
    returnDom.css("display", "inline-flex");
    menu.find(".grid-export-status--success").css("display", "none");
  }

  private static updateProgressBar(element, exported, total) {
    const percent = (exported / total) * 100;
    element.find(".grid-export-status-progress-bar").css("width", `${percent}%`);
    element.find(".grid-export-status-progress-details-text").text(`${exported} lignes exportées`);
    element.find(".grid-export-status-progress-bar-text .exported").text(exported);
    element.find(".grid-export-status-progress-bar-text .total").text(total);
  }
  public title = "";
  public actionMenu;
  public errorMenu;
  public pendingMenu;

  @Prop({
    default: "k-icon k-i-upload",
    type: String
  })
  public iconClass;
  @Prop({
    default: "",
    type: String
  })
  public text;
  @Prop({
    default: "bottom",
    type: String
  })
  public direction;
  @Prop({
    default: null,
    type: Object
  })
  public gridComponent;

  public mounted() {
    this.actionMenu = $(this.$refs.actionMenu)
      .kendoMenu({
        direction: this.direction,
        openOnClick: true,
        select: e => this.onExportActionMenuItemClick(e)
      })
      .data("kendoMenu");
    this.errorMenu = $(this.$refs.errorMenu)
      .kendoMenu({
        direction: this.direction,
        openOnClick: true,
        select: e => this.onExportActionMenuItemClick(e)
      })
      .data("kendoMenu");
    this.pendingMenu = $(this.$refs.pendingMenu)
      .kendoMenu({
        direction: this.direction,
        openOnClick: true,
        select: e => this.onExportActionMenuItemClick(e)
      })
      .data("kendoMenu");
  }

  public export() {
    this.doExport(new jQuery.Event(""), true, $(this.$refs.exportButton));
  }

  @Watch("gridComponent")
  public watchGridComponent(newValue) {
    this.gridComponent = newValue;
    this.setupMenus();
    this.title = this.gridComponent.translations.uploadReport;
    this.gridComponent.kendoGrid.bind("dataBound", () => this.computeTotalExport());
    this.gridComponent.kendoGrid.bind("change", () => this.computeTotalExport());
  }

  private setupMenus() {
    this.setupActionMenu();
    this.setupErrorMenu();
    this.setupPendingMenu();
  }

  private setupActionMenu() {
    const actionSubmenus = [];
    if (this.gridComponent.checkable) {
      actionSubmenus.push({
        attr: {
          "data-export-action": "selection"
        },
        text: this.gridComponent.translations.uploadSelection
      });
    }
    actionSubmenus.push({
      attr: {
        "data-export-action": "all"
      },
      text: this.gridComponent.translations.uploadAllResults
    });
    this.actionMenu.append(actionSubmenus, this.actionMenu.element.find(".k-item.k-first[data-export-menu=root]"));
  }

  private setupErrorMenu() {
    const errorSubmenus = [];
    errorSubmenus.push({
      attr: {
        "data-export-action": "retry"
      },
      text: this.gridComponent.translations.uploadAgain
    });
    errorSubmenus.push({
      attr: {
        "data-export-action": "quit"
      },
      text: this.gridComponent.translations.uploadCancel
    });
    this.errorMenu.append(errorSubmenus, this.errorMenu.element.find(".k-item.k-first[data-export-menu=root]"));
  }

  private setupPendingMenu() {
    const pendingSubmenus = [];
    pendingSubmenus.push({
      attr: {
        "data-export-action": "none"
      },
      encoded: false,
      text: `<div class="export-pending-header">
                  <div class="export-pending-title">
                      <span class="k-icon k-i-upload"></span>
                      <span class="export-pending-title-text">Export en cours...</span>
                  </div>
                  <a  role="button" class=" k-button k-button-icontext export-pending-close">
                      <span class="k-icon k-i-close"></span>
                      <span class="export-pending-close-text">Fermer</span>
                  </a>
                </div>`
    });
    pendingSubmenus.push({
      attr: {
        "data-export-action": "none"
      },
      encoded: false,
      text: `<div class="export-pending-details">
                  <div class="grid-export-status-progress-bar-wrapper">
                      <div class="grid-export-status-progress-bar"></div>
                  </div>
                  <div class="grid-export-status-progress-details-text">Export en cours...</div>
                </div>`
    });
    this.pendingMenu.append(pendingSubmenus, this.pendingMenu.element.find(".k-item.k-first[data-export-menu=root]"));
  }

  private onExportActionMenuItemClick(event) {
    if (this.gridComponent.$(event.item).data("export-menu") !== "root") {
      const exportAction = event.item.dataset.exportAction;
      let disabled;
      switch (exportAction) {
        case "cancel":
          this.cancelExport(event);
          break;
        case "retry":
          this.doExport(event, true);
          break;
        case "selection":
          disabled = this.gridComponent.$(event.item).find(".k-state-disabled");
          if (!disabled.length) {
            this.doExport(event);
          }
          break;
        case "all":
          this.doExport(event, true);
          break;
        case "quit":
          this.displayExportMenu();
          break;
        default:
          break;
      }
    }
  }

  private cancelExport(event: any) {
    event.preventDefault();
    this.displayExportMenu();
  }

  private doExport(event: any, exportAll = false, $exportElement = $(event.sender.element).parent()) {
    event.preventDefault();

    const exportEvent = this.sendExportEvent();
    const queryParams = this.getExportQueryParams(exportAll);
    if (!exportEvent.isDefaultPrevented()) {
      if (exportEvent.serverProgression) {
        GridExportButtonController.displayExportPendingStatus($exportElement, false);
        this.createExportTransaction().then(transaction => {
          this.doTransactionExport(
            transaction,
            queryParams,
            exportEvent.onExport || this.doDefaultExport.bind(this),
            exportEvent.onPolling || this.doDefaultPolling.bind(this)
          );
        });
      } else {
        if (typeof exportEvent.onExport === "function") {
          GridExportButtonController.displayExportPendingStatus($exportElement, true);
          const exportPromise = exportEvent.onExport(null, queryParams);
          if (exportPromise instanceof Promise) {
            exportPromise
              .then(() => {
                this.displayExportSuccessStatus();
              })
              .catch(() => {
                this.displayExportErrorStatus();
              });
          } else {
            this.displayExportErrorStatus();
            this.gridComponent.gridError.error("Export failed: the export function must return a Promise");
          }
        } else {
          this.displayExportErrorStatus();
          this.gridComponent.gridError.error("Export failed: no export function are provided");
        }
      }
    }
  }

  private displayExportMenu() {
    const menu = $(this.$refs.exportButtonWrapper);
    menu.find("ul.grid-export-action-menu").css("display", "");
    menu.find("ul.grid-export-action-menu .k-animation-container").css("display", "none");
    menu.find(".grid-export-status--error").css("display", "none");
    menu.find(".grid-export-status--pending").css("display", "none");
    menu.find(".grid-export-status--success").css("display", "none");
  }

  private sendExportEvent() {
    const event = new GridEvent(
      {
        component: this.gridComponent,
        type: "export"
      },
      null,
      true,
      "GridToolbarActionEvent"
    );
    event.serverProgression = true;
    event.onExport = null;
    event.onPolling = null;
    this.gridComponent.$emit("toolbar-action-click", event);
    return event;
  }

  private sendExportDoneEvent() {
    this.$emit("exportDone");
  }

  private getExportQueryParams(exportAll: any) {
    const gridOptions = this.gridComponent.kendoGrid.getOptions();
    const gridColumns = gridOptions.columns.filter(c => c.field && !c.hidden && c.field !== "icon");
    const dataOptions = Object.assign({}, this.gridComponent.kendoReadOptionsData);
    dataOptions.take = "all";
    delete dataOptions.pageSize;
    const queryParams = this.gridComponent.privateScope.getQueryParamsData(gridColumns, dataOptions);
    queryParams.columnsConfig = gridColumns.map(c => {
      return {
        field: c.field,
        smartType: c.smartType,
        title: c.title
      };
    });
    if (!exportAll) {
      if (this.gridComponent.isFullSelectionState) {
        queryParams.unselectedRows = this.gridComponent.gridDataUtils.getUncheckRowsList();
      } else {
        queryParams.selectedRows = this.gridComponent.kendoGrid.selectedKeyNames();
      }
    }
    return queryParams;
  }

  private createExportTransaction() {
    return this.gridComponent.$http
      .post("/api/v2/grid/export")
      .then(response => {
        return response.data.data;
      })
      .catch(err => {
        this.displayExportErrorStatus();
        this.gridComponent.gridError.error(err);
      });
  }

  private displayExportErrorStatus() {
    const menu = $(this.$refs.exportButtonWrapper);
    menu.find("ul.grid-export-action-menu").css("display", "none");
    menu.find(".grid-export-status--error .grid-export-status-text").text(this.gridComponent.translations.uploadError);
    menu.find(".grid-export-status--error").css("display", "inline-flex");
    menu.find(".grid-export-status--pending").css("display", "none");
    menu.find(".grid-export-status--success").css("display", "none");
  }

  /**
   * Do the transaction based export action
   * @param transaction
   * @param queryParams
   * @param exportRequest
   * @param pollingRequest
   */
  private doTransactionExport(transaction, queryParams, exportRequest, pollingRequest) {
    const transactionId = transaction.transactionId;
    if (typeof exportRequest === "function") {
      exportRequest(transaction, queryParams);
      this.pollTransaction(transactionId, pollingRequest);
    } else {
      this.displayExportErrorStatus();
      this.gridComponent.gridError.error("Export failed: no export function are provided");
    }
  }

  // tslint:disable-next-line:no-empty
  private pollTransaction(transactionId, pollingCb = () => {}, pollingTime = 500) {
    let timer = null;
    const getStatus = () => {
      this.gridComponent.$http
        .get(`/api/v2/ui/transaction/${transactionId}/status`)
        .then(response => {
          const responseData = response.data.data;
          const progressBar = this.gridComponent.$(".grid-export-status--pending", this.gridComponent.$el);
          if (responseData.transactionStatus === "PENDING" || responseData.transactionStatus === "CREATED") {
            if (typeof pollingCb === "function") {
              // @ts-ignore
              pollingCb(responseData, progressBar);
            }
            timer = setTimeout(getStatus, pollingTime);
          } else {
            if (typeof pollingCb === "function") {
              // @ts-ignore
              pollingCb(responseData, progressBar);
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

  private doDefaultExport(transaction, queryParams) {
    const transactionId = transaction.transactionId;
    const exportUrl = this.gridComponent.resolveExportUrl.replace("<transaction>", transactionId);
    if (!exportUrl) {
      this.displayExportErrorStatus();
      this.gridComponent.gridError.error("Export failed: the default export url cannot be used");
    } else {
      this.gridComponent.$http
        .get(this.gridComponent.resolveExportUrl.replace("<transaction>", transactionId), {
          params: queryParams,
          paramsSerializer: params => this.gridComponent.$.param(params),
          responseType: "blob",
          timeout: 0
        })
        .then(response => this.downloadExportFile(response.data))
        .catch(err => {
          this.displayExportErrorStatus();
          this.gridComponent.gridError.error(err);
        });
    }
  }

  private downloadExportFile(blobFile) {
    const url = window.URL.createObjectURL(blobFile);
    let link;
    const existLink = this.gridComponent.$("a.seGridExportLink");
    if (existLink.length) {
      link = existLink[0];
    } else {
      link = document.createElement("a");
      link.classList.add("seGridExportLink");

      document.body.appendChild(link);
    }
    link.setAttribute(
      "download",
      `${this.gridComponent.collectionProperties.title || this.gridComponent.collection || "data"}.xlsx`
    );
    link.href = url;
    link.click();
  }

  private doDefaultPolling(transaction, progressBar) {
    let exportedRows = 0;
    let total = this.gridComponent.kendoGrid.dataSource.total();
    if (transaction.transactionStatus === "PENDING") {
      exportedRows = transaction.details.exportedRows || 0;

      if (transaction.details.totalRows) {
        total = transaction.details.totalRows;
      }
    } else if (transaction.transactionStatus === "DONE") {
      this.displayExportSuccessStatus();
    } else if (transaction.transactionStatus === "ERROR") {
      this.displayExportErrorStatus();
    }
    GridExportButtonController.updateProgressBar(progressBar, exportedRows, total);
  }

  private displayExportSuccessStatus(autoHide = true) {
    const menu = $(this.$refs.exportButtonWrapper);
    menu.find("ul.grid-export-action-menu").css("display", "none");
    menu.find(".grid-export-status--error").css("display", "none");
    menu.find(".grid-export-status--pending").css("display", "none");
    menu
      .find(".grid-export-status--success .grid-export-status-text")
      .text(this.gridComponent.translations.uploadSuccess);
    menu.find(".grid-export-status--success").css("display", "inline-flex");
    if (autoHide) {
      setTimeout(() => {
        this.displayExportMenu();
      }, 1000);
    }

    this.sendExportDoneEvent();
  }

  private computeTotalExport() {
    const grid = this.gridComponent.kendoGrid;
    const selectedRows = grid.selectedKeyNames();
    const countTotals = grid.dataSource.total();
    let countRows = countTotals;
    if (!this.gridComponent.isFullSelectionState) {
      countRows = selectedRows.length ? selectedRows.length : 0;
    } else {
      countRows = countRows - this.gridComponent.gridDataUtils.getUncheckRowsList().length;
    }

    const exportSelection = this.actionMenu.element.find(".k-item[data-export-action=selection] .k-link");
    const exportAll = this.actionMenu.element.find(".k-item[data-export-action=all] .k-link");

    const template = count => `<span class="export-total">${count}</span>`;
    let totalExport = exportSelection.find(".export-total");
    if (totalExport.length) {
      totalExport.replaceWith(template(countRows));
    } else {
      exportSelection.append(template(countRows));
    }

    if (countRows === 0) {
      this.actionMenu.enable(exportSelection, false);
    } else {
      this.actionMenu.enable(exportSelection, true);
    }

    totalExport = exportAll.find(".export-total");
    if (totalExport.length) {
      totalExport.replaceWith(template(countTotals));
    } else {
      exportAll.append(template(countTotals));
    }
  }
}
