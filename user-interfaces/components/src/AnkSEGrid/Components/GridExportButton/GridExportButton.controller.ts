import "@progress/kendo-ui/js/kendo.menu";
import $ from "jquery";
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";
@Component({
  name: "ank-se-grid-export-button"
})
export default class GridExportButtonController extends Vue {
  public title = "";
  public actionMenu;
  public errorMenu;
  public pendingMenu;
  public actionMenuVisible = true;
  public errorMenuVisible = false;
  public pendingMenuVisible = false;
  public successMenuVisible = false;

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
    this.gridComponent.export(true, true, this.doDefaultPolling);
  }

  @Watch("gridComponent")
  public watchGridComponent(newValue) {
    this.gridComponent = newValue;
    this.setupMenus();
    this.title = this.gridComponent.translations.uploadReport;
    this.gridComponent.kendoGrid.bind("dataBound", () => this.computeTotalExport());
    this.gridComponent.kendoGrid.bind("change", () => this.computeTotalExport());
    this.gridComponent.$on("before-polling-grid-export", () => this.displayExportPendingStatus(false));
    this.gridComponent.$on("grid-export-error", this.displayExportErrorStatus);
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
          this.gridComponent.export(true, true, this.doDefaultPolling);
          break;
        case "selection":
          disabled = this.gridComponent.$(event.item).find(".k-state-disabled");
          if (!disabled.length) {
            this.gridComponent.export(false, true, this.doDefaultPolling);
          }
          break;
        case "all":
          this.gridComponent.export(true, true, this.doDefaultPolling);
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

  private displayExportMenu() {
    this.actionMenuVisible = true;
    this.errorMenuVisible = false;
    this.pendingMenuVisible = false;
    this.successMenuVisible = false;
  }

  private sendExportDoneEvent() {
    this.$emit("exportDone");
  }

  private doDefaultPolling(transaction) {
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
    this.updateProgressBar(exportedRows, total);
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

  private displayExportPendingStatus(indeterminate = false) {
    this.actionMenuVisible = false;
    this.errorMenuVisible = false;
    this.successMenuVisible = false;
    this.pendingMenuVisible = true;
    const pendingMenuDom = this.pendingMenu.element;
    if (indeterminate) {
      pendingMenuDom
        .find(".grid-export-status-progress-bar-wrapper")
        .addClass("grid-export-status-progress-bar-wrapper--indeterminate");
      pendingMenuDom.find(".grid-export-status-progress-bar").css("width", "40");
    } else {
      pendingMenuDom
        .find(".grid-export-status-progress-bar-wrapper")
        .removeClass("grid-export-status-progress-bar-wrapper--indeterminate");
      pendingMenuDom.find(".grid-export-status-progress-bar").css("width", "0");
    }
  }

  private updateProgressBar(exported, total) {
    const percent = (exported / total) * 100;
    const progressBar = $(this.$refs.pendingMenu);
    progressBar.find(".grid-export-status-progress-bar").css("width", `${percent}%`);
    progressBar.find(".grid-export-status-progress-details-text").text(`${exported} lignes exportées`);
    progressBar.find(".grid-export-status-progress-bar-text .exported").text(exported);
    progressBar.find(".grid-export-status-progress-bar-text .total").text(total);
  }

  private displayExportSuccessStatus(autoHide = true) {
    this.actionMenuVisible = false;
    this.errorMenuVisible = false;
    this.pendingMenuVisible = false;
    this.successMenuVisible = true;
    $(this.$refs.successMenuText).text(this.gridComponent.translations.uploadSuccess);
    if (autoHide) {
      setTimeout(() => {
        this.displayExportMenu();
      }, 1000);
    }
    this.sendExportDoneEvent();
  }

  private displayExportErrorStatus() {
    const menu = $(this.$refs.exportButtonWrapper);
    menu.find("ul.grid-export-action-menu").css("display", "none");
    this.actionMenuVisible = false;
    this.errorMenuVisible = true;
    $(this.$refs.errorMenuText).text(this.gridComponent.translations.uploadError);
    this.pendingMenuVisible = false;
    this.successMenuVisible = false;
  }
}
