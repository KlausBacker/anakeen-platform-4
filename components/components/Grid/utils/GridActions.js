import GridEvent from "./GridEvent";
import AbstractGridUtil from "./AbstractGridUtil";
// import ToolbarActionTemplate from "../templates/GridToolbarAction.template.kd";

export const DEFAULT_ACTION_PROPS = {
  edit: {},
  consult: {},
  custom: {},
  export: {
    iconClass: "k-icon k-i-upload"
  },
  columns: {
    iconClass: "k-icon k-i-custom"
  }
};

export default class GridActions extends AbstractGridUtil {
  getAction(actionName) {
    const actionMethod = `${actionName}Action`;
    const actionObject = {};
    actionObject.title = this.vueComponent.translations[actionName];
    actionObject.iconClass = DEFAULT_ACTION_PROPS[actionName]
      ? DEFAULT_ACTION_PROPS[actionName].iconClass
      : "";
    if (typeof this[actionMethod] === "function") {
      actionObject.click = this[actionMethod].bind(this);
    } else {
      actionObject.click = this.customAction.bind(this);
    }
    return actionObject;
  }

  getToolbarAction(toolbarActionName) {
    const actionMethod = `${toolbarActionName}ToolbarAction`;
    const actionObject = {};
    actionObject.title =
      this.vueComponent.translations[toolbarActionName] || "";
    actionObject.iconClass = DEFAULT_ACTION_PROPS[toolbarActionName]
      ? DEFAULT_ACTION_PROPS[toolbarActionName].iconClass
      : "";
    if (typeof this[actionMethod] === "function") {
      actionObject.click = this[actionMethod].bind(this);
    } else {
      actionObject.click = this.customToolbarAction.bind(this);
    }
    return actionObject;
  }

  editAction(e) {
    e.preventDefault();
    const target = e.currentTarget || e.item || e.target;
    const item = this.vueComponent.kendoGrid.dataItem(
      this.vueComponent.$(target).closest("tr")
    ).rowData;
    const event = new GridEvent(
      {
        type: "edit",
        row: item
      },
      target,
      true,
      "GridActionEvent"
    );
    const id = item.initid || item.id;
    this.vueComponent.$emit("action-click", event);
    if (!event.isDefaultPrevented()) {
      window.open(
        `/api/v2/smart-elements/${id}/views/!defaultEdition.html`,
        "_blank"
      );
    }
  }

  consultAction(e) {
    if (typeof e.preventDefault === "function") {
      e.preventDefault();
    }
    const target = e.currentTarget || e.item || e.target;
    const item = this.vueComponent.kendoGrid.dataItem(
      this.vueComponent.$(target).closest("tr")
    ).rowData;
    const event = new GridEvent(
      {
        type: "consult",
        row: item
      },
      target,
      true,
      "GridActionEvent"
    );
    this.vueComponent.$emit("action-click", event);
    const id = item.initid || item.id;
    if (!event.isDefaultPrevented()) {
      window.open(
        `/api/v2/smart-elements/${id}/views/!defaultConsultation.html`,
        "_blank"
      );
    }
  }

  customAction(e, actionType) {
    e.preventDefault();
    const target = e.currentTarget || e.item || e.target;
    if (actionType) {
      const item = this.vueComponent.kendoGrid.dataItem(
        this.vueComponent.$(target).closest("tr")
      ).rowData;
      const event = new GridEvent(
        { type: actionType, row: item },
        target,
        false,
        "GridActionEvent"
      );
      this.vueComponent.$emit("action-click", event);
    }
  }

  displayExportMenu() {
    const menu = this.vueComponent.kendoGrid.element;
    menu.find("ul.grid-export-action-menu").css("display", "inline-flex");
    menu.find(".grid-export-status--error").css("display", "none");
    menu.find(".grid-export-status--pending").css("display", "none");
    menu.find(".grid-export-status--success").css("display", "none");
  }

  displayExportPendingStatus(indeterminate = false) {
    const menu = this.vueComponent.kendoGrid.element;
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

  displayExportSuccessStatus(autoHide = true) {
    const menu = this.vueComponent.kendoGrid.element;
    menu.find("ul.grid-export-action-menu").css("display", "none");
    menu.find(".grid-export-status--error").css("display", "none");
    menu.find(".grid-export-status--pending").css("display", "none");
    menu.find(".grid-export-status--success").css("display", "inline-flex");
    if (autoHide) {
      setTimeout(() => {
        this.displayExportMenu();
      }, 1000);
    }
  }

  displayExportErrorStatus() {
    const menu = this.vueComponent.kendoGrid.element;
    menu.find("ul.grid-export-action-menu").css("display", "none");
    menu.find(".grid-export-status--error").css("display", "inline-flex");
    menu.find(".grid-export-status--pending").css("display", "none");
    menu.find(".grid-export-status--success").css("display", "none");
  }

  pollTransaction(transactionId, pollingCb = () => {}, pollingTime = 500) {
    let timer = null;
    let call = 0;
    const getStatus = () => {
      call++;
      this.vueComponent.$http
        .get(`/api/v2/ui/transaction/${transactionId}/status`)
        .then(response => {
          const responseData = response.data.data;
          const progressBar = this.vueComponent.$(
            ".grid-export-status--pending",
            this.vueComponent.$el
          );
          if (
            (responseData.transactionStatus === "PENDING" ||
              responseData.transactionStatus === "CREATED") &&
            call < 20
          ) {
            if (typeof pollingCb === "function") {
              pollingCb(responseData, progressBar);
            }
            timer = setTimeout(getStatus, pollingTime);
          } else {
            if (typeof pollingCb === "function") {
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

  downloadExportFile(blobFile) {
    const date = new Date();
    const horodator =
      ("0" + date.getDate()).slice(-2) +
      "-" +
      ("0" + (date.getMonth() + 1)).slice(-2) +
      "-" +
      date.getFullYear();
    const url = window.URL.createObjectURL(blobFile);
    let link;
    const existLink = this.vueComponent.$("a.seGridExportLink");
    if (existLink.length) {
      link = existLink[0];
    } else {
      link = document.createElement("a");
      link.classList.add("seGridExportLink");

      document.body.appendChild(link);
    }
    link.setAttribute(
      "download",
      `export-${this.vueComponent.collection || "data"}-${horodator}.xlsx`
    );
    link.href = url;
    link.click();
  }

  updateProgressBar(element, exported, total) {
    const percent = (exported / total) * 100;
    element
      .find(".grid-export-status-progress-bar")
      .css("width", `${percent}%`);
    element
      .find(".grid-export-status-progress-details-text")
      .text(`${exported} lignes exportées`);
    element
      .find(".grid-export-status-progress-bar-text .exported")
      .text(exported);
    element.find(".grid-export-status-progress-bar-text .total").text(total);
  }

  /**
   * Send event for the export action
   * @return {GridEvent}
   */
  sendExportEvent() {
    const event = new GridEvent(
      {
        type: "export",
        component: this.vueComponent
      },
      null,
      true,
      "GridToolbarActionEvent"
    );
    event.serverProgression = true;
    event.onExport = null;
    event.onPolling = null;
    this.vueComponent.$emit("toolbar-action-click", event);
    return event;
  }

  /**
   * Create a transaction on the server
   * @return {*|{catch, then}|Promise<T | never>}
   */
  createExportTransaction() {
    return this.vueComponent.$http
      .post("/api/v2/grid/export")
      .then(response => {
        return response.data.data;
      })
      .catch(err => {
        this.displayExportErrorStatus();
        this.vueComponent.gridError.error(err);
      });
  }

  /**
   * Do the transaction based export action
   * @param transaction
   * @param queryParams
   * @param exportRequest
   * @param pollingRequest
   */
  doTransactionExport(transaction, queryParams, exportRequest, pollingRequest) {
    const transactionId = transaction.transactionId;
    if (typeof exportRequest === "function") {
      exportRequest(transaction, queryParams);
      this.pollTransaction(transactionId, pollingRequest);
    } else {
      this.displayExportErrorStatus();
      this.vueComponent.gridError.error(
        "Export failed: no export function are provided"
      );
    }
  }

  getExportQueryParams(exportAll) {
    const gridOptions = this.vueComponent.kendoGrid.getOptions();
    const gridColumns = gridOptions.columns.filter(
      c => c.field && !c.hidden && c.field !== "icon"
    );
    const dataOptions = Object.assign(
      {},
      this.vueComponent.kendoReadOptionsData
    );
    dataOptions.take = "all";
    delete dataOptions.pageSize;
    const queryParams = this.vueComponent.privateScope.getQueryParamsData(
      gridColumns,
      dataOptions
    );
    queryParams.columnsConfig = gridColumns.map(c => {
      return {
        title: c.title,
        smartType: c.smartType,
        field: c.field
      };
    });
    if (!exportAll) {
      if (this.vueComponent.isFullSelectionState) {
        queryParams.unselectedRows = this.vueComponent.gridDataUtils.getUncheckRowsList();
      } else {
        queryParams.selectedRows = this.vueComponent.kendoGrid.selectedKeyNames();
      }
    }
    return queryParams;
  }

  doExport(event, exportAll) {
    event.preventDefault();
    const exportEvent = this.sendExportEvent();
    const queryParams = this.getExportQueryParams(exportAll);
    if (exportEvent.serverProgression) {
      this.displayExportPendingStatus();
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
        this.displayExportPendingStatus(true);
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
          this.vueComponent.gridError.error(
            "Export failed: the export function must return a Promise"
          );
        }
      } else {
        this.displayExportErrorStatus();
        this.vueComponent.gridError.error(
          "Export failed: no export function are provided"
        );
      }
    }
  }

  doDefaultExport(transaction, queryParams) {
    const transactionId = transaction.transactionId;
    const exportUrl = this.vueComponent.resolveExportUrl.replace(
      "<transaction>",
      transactionId
    );
    if (!exportUrl) {
      this.displayExportErrorStatus();
      this.vueComponent.gridError.error(
        "Export failed: the default export url cannot be used"
      );
    } else {
      this.vueComponent.$http
        .get(
          this.vueComponent.resolveExportUrl.replace(
            "<transaction>",
            transactionId
          ),
          {
            responseType: "blob",
            params: queryParams,
            paramsSerializer: params => this.vueComponent.$.param(params)
          }
        )
        .then(response => this.downloadExportFile(response.data))
        .catch(err => {
          this.displayExportErrorStatus();
          this.vueComponent.gridError.error(err);
        });
    }
  }

  doDefaultPolling(transaction, progressBar) {
    let exportedRows = 0;
    let total = this.vueComponent.kendoGrid.dataSource.total();
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
    this.updateProgressBar(progressBar, exportedRows, total);
  }

  cancelExport(event) {
    event.preventDefault();
    this.displayExportMenu();
  }

  onExportActionMenuItemClick(event) {
    if (this.vueComponent.$(event.item).data("export-menu") !== "root") {
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
          disabled = this.vueComponent.$(event.item).find(".k-state-disabled");
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

  initToolbarExportPendingMenuTemplate() {
    const exportMenu = this.vueComponent.kendoGrid.element
      .find("ul.grid-export-status--pending")
      .kendoMenu({
        openOnClick: true,
        select: e => this.onExportActionMenuItemClick(e)
      })
      .data("kendoMenu");
    exportMenu.append(
      [
        {
          text: `<div class="export-pending-header">
                  <div class="export-pending-title">
                      <span class="k-icon k-i-upload"></span>
                      <span class="export-pending-title-text">Export en cours...</span>
                  </div>
                  <a  role="button" class=" k-button k-button-icontext export-pending-close">
                      <span class="k-icon k-i-close"></span>
                      <span class="export-pending-close-text">Fermer</span>
                  </a>
                </div>`,
          encoded: false,
          attr: {
            "data-export-action": "none"
          }
        },
        {
          text: `<div class="export-pending-details">
                  <div class="grid-export-status-progress-bar-wrapper">
                      <div class="grid-export-status-progress-bar"></div>
                  </div>
                  <div class="grid-export-status-progress-details-text">Export en cours...</div>
                </div>`,
          encoded: false,
          attr: {
            "data-export-action": "none"
          }
        }
      ],
      exportMenu.element.find(".k-item.k-first[data-export-menu=root]")
    );
  }

  initToolbarExportErrorMenuTemplate() {
    const exportMenu = this.vueComponent.kendoGrid.element
      .find("ul.grid-export-status--error")
      .kendoMenu({
        openOnClick: true,
        select: e => this.onExportActionMenuItemClick(e)
      })
      .data("kendoMenu");
    exportMenu.append(
      [
        {
          text: "Recommencer l'export",
          attr: {
            "data-export-action": "retry"
          }
        },
        {
          text: "Annuler",
          attr: {
            "data-export-action": "quit"
          }
        }
      ],
      exportMenu.element.find(".k-item.k-first[data-export-menu=root]")
    );
  }

  initToolbarExportActionMenuTemplate() {
    const exportMenu = this.vueComponent.kendoGrid.element
      .find("ul.grid-export-action-menu")
      .kendoMenu({
        openOnClick: true,
        select: e => this.onExportActionMenuItemClick(e)
      })
      .data("kendoMenu");
    const submenus = [];
    if (this.vueComponent.checkable) {
      submenus.push({
        text: "Seulement la sélection",
        attr: {
          "data-export-action": "selection"
        }
      });
    }
    submenus.push({
      text: "Toute la grille",
      attr: {
        "data-export-action": "all"
      }
    });
    exportMenu.append(
      submenus,
      exportMenu.element.find(".k-item.k-first[data-export-menu=root]")
    );
  }

  initToolbarExportTemplate() {
    this.initToolbarExportActionMenuTemplate();
    this.initToolbarExportErrorMenuTemplate();
    this.initToolbarExportPendingMenuTemplate();
  }

  exportToolbarAction(e, actionType) {
    e.preventDefault();
    const target = e.currentTarget || e.item || e.target;
    const event = new GridEvent(
      {
        type: actionType,
        component: this.vueComponent
      },
      target,
      true,
      "GridToolbarActionEvent"
    );
    event.onExport = null;
    this.vueComponent.$emit("toolbar-action-click", event);
    if (!event.isDefaultPrevented()) {
      // Do export
      // this.displayExportPopup(e);
    }
  }

  columnsToolbarAction(e, actionType) {
    e.preventDefault();
    const target = e.currentTarget || e.item || e.target;
    const event = new GridEvent(
      {
        type: actionType,
        grid: this.vueComponent.kendoGrid
      },
      target,
      true,
      "GridToolbarActionEvent"
    );
    this.vueComponent.$emit("toolbar-action-click", event);
    if (!event.cancelable || !event.defaultPrevented) {
      if (this.vueComponent.$refs.gridColumnsDialog) {
        this.vueComponent.$refs.gridColumnsDialog.open();
      }
    }
  }

  customToolbarAction(e, actionType) {
    e.preventDefault();
    const target = e.currentTarget || e.item || e.target;
    if (actionType) {
      const event = new GridEvent(
        { type: actionType, grid: this.vueComponent.kendoGrid },
        target,
        false,
        "GridToolbarActionEvent"
      );
      this.vueComponent.$emit("toolbar-action-click", event);
    }
  }
}
