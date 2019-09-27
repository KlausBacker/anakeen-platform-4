import GridEvent from "./GridEvent";

export default class GridExportUtils {
  constructor(grid) {
    this.gridComponent = grid;
  }

  export(
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
      const queryParams = this.getExportQueryParams(exportAll);
      if (typeof onExport === "function") {
        this.sendBeforePollingEvent();
        let promise = this.createExportTransaction()
          .then(transaction => {
            return this.doTransactionExport(transaction, queryParams, exportCb, pollingCb, pollingTime, directDownload);
          })
          .then(result => {
            return result ? result.data : true;
          });
        if (!directDownload) {
          return promise;
        }
      } else {
        this.gridComponent.gridError.error("Export failed: no export function are provided");
        this.sendErrorEvent("Export failed: no export function are provided");
      }
    }
  }

  sendBeforeExportEvent(onExport, onPolling) {
    const event = new GridEvent({
      component: this.gridComponent,
      type: "export"
    });
    event.onExport = onExport;
    event.onPolling = onPolling;
    this.gridComponent.$emit("before-grid-export", event);
    return event;
  }

  sendBeforePollingEvent() {
    const event = new GridEvent(null, null, false);
    this.gridComponent.$emit("before-polling-grid-export", event);
    return event;
  }

  sendErrorEvent(message) {
    const event = new GridEvent(
      {
        message: message
      },
      null,
      false
    );
    this.gridComponent.$emit("grid-export-error", event);
    return event;
  }

  getExportQueryParams(exportAll) {
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

  createExportTransaction() {
    return this.gridComponent.$http
      .post("/api/v2/grid/export")
      .then(response => {
        return response.data.data;
      })
      .catch(err => {
        this.gridComponent.gridError.error(err);
        this.sendErrorEvent(err);
      });
  }

  doTransactionExport(transaction, queryParams, exportRequest, pollingRequest, pollingTime, directDownload) {
    const transactionId = transaction.transactionId;
    let file = exportRequest(transaction, queryParams, directDownload);
    this.pollTransaction(transactionId, pollingRequest, pollingTime);
    return file;
  }

  pollTransaction(transactionId, pollingCb, pollingTime) {
    let timer = null;
    const getStatus = () => {
      this.gridComponent.$http
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

  doDefaultExport(transaction, queryParams, directDownload) {
    const transactionId = transaction.transactionId;
    const exportUrl = this.gridComponent.resolveExportUrl.replace("<transaction>", transactionId);
    if (!exportUrl) {
      this.gridComponent.gridError.error("Export failed: the default export url cannot be used");
    } else {
      let filePromise = this.gridComponent.$http.get(exportUrl, {
        params: queryParams,
        paramsSerializer: params => this.gridComponent.$.param(params),
        responseType: "blob",
        timeout: 0
      });
      if (directDownload) {
        return filePromise
          .then(response => this.downloadExportFile(response.data))
          .catch(err => {
            this.gridComponent.gridError.error(err);
            this.sendErrorEvent(err);
          });
      } else {
        return filePromise;
      }
    }
  }

  downloadExportFile(blobFile) {
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
}
