import AbstractGridUtil from "./AbstractGridUtil";
import GridFilter from "./GridFilter";
import GridEvent from "./GridEvent";
import ExportActionTemplate from "../templates/GridToolbarExportAction.template.kd";

export default class GridKendoUtils extends AbstractGridUtil {
  onGridDataBound(dataBoundEvent) {
    const allMenus = this.vueComponent.$(".actionMenu", dataBoundEvent.content);
    this.createKendoActionMenu(allMenus);
    allMenus.on("mousedown", e => this.onKendoActionMenuMouseDown(e));

    if (this.vueComponent.isFullSelectionState) {
      this.vueComponent.kendoGrid.select("tr");
    }
    const event = new GridEvent(
      {
        kendoWidget: dataBoundEvent.sender
      },
      null,
      false
    );
    this.vueComponent.$emit("grid-data-bound", event);
  }

  createKendoActionMenu(menus) {
    const kendoMenuOptions = {
      openOnClick: {
        rootMenuItems: true
      },
      select: e => this.onActionMenuClick(e)
    };
    menus.each((index, element) => {
      this.vueComponent.$(element).kendoMenu(kendoMenuOptions);
    });
  }

  onKendoActionMenuMouseDown(e) {
    const kendoMenu = this.vueComponent.$(e.currentTarget).data("kendoMenu");
    const $grid = this.vueComponent.$(this.vueComponent.$refs.kendoGrid);
    const $gridContent = $grid.find(".k-grid-content");
    const $menu = this.vueComponent.$(e.currentTarget);
    const menuOffset = $menu.offset();
    const gridContentOffset = $gridContent.offset();
    const threshold = 85;
    const remainingSpace =
      gridContentOffset.top +
      $gridContent.height() -
      (menuOffset.top + $menu.height());

    if (remainingSpace < threshold) {
      kendoMenu.setOptions({
        ...kendoMenu.options,
        direction: "top"
      });
    } else {
      kendoMenu.setOptions({
        ...kendoMenu.options,
        direction: "bottom"
      });
    }
  }

  onActionMenuClick(e) {
    const actionType = e.item.dataset.actiontype;
    if (actionType) {
      const action = this.vueComponent.gridActions.getAction(actionType);
      action.click(e, actionType);
    }
  }

  createKendoWidget(gridOptions = {}) {
    if (gridOptions.height === "100%") {
      gridOptions.height = "";
    }
    this.vueComponent.kendoGrid = this.vueComponent
      .$(this.vueComponent.$refs.kendoGrid)
      .kendoGrid(gridOptions)
      .data("kendoGrid");

    this.vueComponent.$(window).resize(() => {
      this.resizeKendoWidgets();
    });
    this.resizeKendoWidgets();

    if (!gridOptions.height) {
      this.vueComponent
        .$(this.vueComponent.$refs.gridWrapper)
        .addClass("smart--fit");
    }

    this.vueComponent.$once("grid-ready", () => {
      if (this.vueComponent.kendoGrid.pager) {
        const pageSizes = this.vueComponent.kendoGrid.pager.element.find(
          ".k-pager-sizes .k-dropdown [data-role=dropdownlist]"
        );
        if (pageSizes && pageSizes.length) {
          pageSizes.data("kendoDropDownList").bind("change", () => {
            this.vueComponent.privateScope.notifyChange();
          });
        }
      }
    });
  }

  resizeKendoWidgets() {
    if (this.vueComponent.kendoGrid) {
      this.vueComponent.kendoGrid.resize();
    }
    if (this.vueComponent.$refs.gridColumnsDialog) {
      this.vueComponent.$refs.gridColumnsDialog.resize();
    }
  }

  prepareKendoGridToolbar(config) {
    if (config.toolbar) {
      if (config.toolbar.actionConfigs && config.toolbar.actionConfigs.length) {
        this.vueComponent.kendoGridOptions.toolbar = [];
        config.toolbar.actionConfigs.forEach(a => {
          const toolbarAction = this.vueComponent.gridActions.getToolbarAction(
            a.action
          );
          const toolbarActionConfig = {
            name: a.action,
            text: a.title || toolbarAction.title,
            iconClass: a.iconClass || toolbarAction.iconClass,
            className:
              "grid-toolbar-action grid-toolbar-" + a.action + "-action",
            attributes: { "data-action-id": a.action }
          };
          if (a.action === "export") {
            toolbarActionConfig.template = ExportActionTemplate;
            this.vueComponent.$once("grid-ready", () => {
              this.vueComponent.gridActions.initToolbarExportTemplate();
            });
          }
          this.vueComponent.kendoGridOptions.toolbar.push(toolbarActionConfig);
        });
      }
    }
  }

  decodeHtml(html) {
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
  }
  /**
   * Format column to the Kendo Grid Column object format
   * @param {object} col - Column configuration
   * @param {object} config - Grid configuration
   */
  formatKendoGridColumn(col, config) {
    const type = col.smartType;
    if (col.encoded === undefined && type === "htmltext") {
      col.encoded = false;
    }
    if (col.field) {
      col.headerAttributes = {
        class: this.vueComponent.contextTitles ? "grid-column--context" : ""
      };
      if (!col.sortable) {
        col.headerAttributes.class += " grid-column-no-sortable";
      }
      col.attributes = {
        class: `grid-cell--${col.field}`
      };
    }

    if (
      col.filterable &&
      col.filterable.cell &&
      col.filterable.cell.enable === true &&
      this.vueComponent.filterable === "menu"
    ) {
      if (col.headerAttributes && col.headerAttributes.class) {
        col.headerAttributes.class += " grid-column-menu-filter";
      } else {
        col.headerAttributes = {
          class: "grid-column-menu-filter"
        };
      }
    }

    if (col.field === "icon") {
      col.width = "4.5rem";
      if (col.headerAttributes && col.headerAttributes.class) {
        col.headerAttributes.class += " grid-cell-align-center";
      } else {
        col.headerAttributes = {
          class: "grid-cell-align-center"
        };
      }
      col.attributes.class += " grid-cell-align-center";
    }
    if (
      col.filterable &&
      col.filterable.cell &&
      col.filterable.cell.enable === true
    ) {
      col.filterable.ui = element => {
        col.filterable.cell.template({
          element: element
        });
      };
      col.filterable.messages = col.filterable.messages || {};
      col.filterable.messages.info = col.title;
      col.filterable.cell.template = e => {
        return this.vueComponent.gridFilter.getColumnFilterTemplate(e, col);
      };
    }
    col.subTitle = "";
    if (
      this.vueComponent.contextTitles &&
      col.withContext &&
      col.context &&
      col.context.length
    ) {
      const title = col.title || col.field;
      const titleWords = col.context
        .filter((value, index, self) => {
          return self.indexOf(value) === index;
        })
        .join(` ${this.vueComponent.contextTitlesSeparator} `);
      col.title = title;
      col.subTitle = titleWords;
    }
    if (!col.template) {
      col.template = this.vueComponent.gridDataUtils.formatAnkAttributesValue(
        col
      );
    }

    if (!col.headerTemplate) {
      if (col.subTitle) {
        col.fullTitle = `${col.subTitle} / ${col.title}`;
      } else {
        col.fullTitle = col.title;
      }
      if (this.vueComponent.contextTitles) {
        col.headerTemplate = kendo.template(
          `<div class="grid-header-content" title="#: fullTitle #"><div class="grid-header--subtitle">#: subTitle #</div><div class="grid-header--title" >#: title #</div></div>`
        )(col);
      }
    }

    if (config.footer && config.footer[col.field]) {
      col.footerValue = config.footer[col.field];

      col.footerTemplate = kendo.template(
        `<div class="grid-footer--title grid-foot-content--#: smartType #"> #: footerValue # </div>`
      )(col);
    }
  }

  /**
   * Compute kendo grid columns
   * @param config - Grid configuration
   */
  prepareKendoGridColumns(config) {
    if (this.vueComponent.checkable) {
      this.prepareCheckableColumn();
    }
    if (config.smartFields && config.smartFields.length) {
      this.vueComponent.kendoGridOptions.columns = this.vueComponent.kendoGridOptions.columns.concat(
        config.smartFields
      );
      this.vueComponent.kendoGridOptions.columns.forEach(col =>
        this.formatKendoGridColumn(col, config)
      );
    }
  }

  /**
   * Compute checkable column
   */
  prepareCheckableColumn() {
    this.vueComponent.kendoGridOptions.columns.push({
      selectable: true,
      width: "50px",
      attributes: {
        class: "checkable-grid-cell grid-cell-align-center"
      },
      headerAttributes: {
        class: "checkable-grid-header grid-cell-align-center toggle-all-rows",
        "data-id": "ank-se-grid-checkable"
      }
    });

    this.vueComponent.$once("grid-ready", () => {
      this.vueComponent.kendoGrid.thead.on(
        "click",
        ".toggle-all-rows input",
        e => {
          this.vueComponent.allRowsSelectable = e.currentTarget
            ? !!e.currentTarget.checked
            : false;
          if (!this.vueComponent.allRowsSelectable) {
            this.vueComponent.kendoGrid._selectedIds = {};
            this.vueComponent.uncheckRows = {};
            this.vueComponent.kendoGrid.clearSelection();
          }
        }
      );
      this.vueComponent.kendoGrid.content.on(
        "click",
        ".checkable-grid-cell input",
        e => {
          const item = this.vueComponent.kendoGrid.dataItem(
            this.vueComponent.$(e.currentTarget).closest("tr")
          ).rowData;

          if (this.vueComponent.isFullSelectionState) {
            this.vueComponent.uncheckRows[item.initid.toString()] = !e
              .currentTarget.checked;
          }
        }
      );
    });
  }

  /**
   * Compute the kendo grid actions
   * @param config - Grid configuration
   */
  prepareKendoGridActions(config) {
    if (config.actions && config.actions.actionConfigs) {
      this.vueComponent.kendoGridOptions.columns.push(
        this.vueComponent.gridDataUtils.formatActionsColumn(config.actions)
      );
    }
  }

  prepareKendoGridPaging(config) {
    if (config.pageable && config.pageable.pageSize) {
      this.vueComponent.kendoGridOptions.pageable.pageSize =
        config.pageable.pageSize;
      this.vueComponent.kendoDataSourceOptions.pageSize =
        config.pageable.pageSize;
      if (typeof config.pageable.pageSizes !== "undefined") {
        this.vueComponent.kendoGridOptions.pageable.pageSizes =
          config.pageable.pageSizes;
      }
    }
  }
  /**
   * Compute the kendo grid actions
   * @param config - Grid configuration
   */
  prepareKendoGridLocales(config) {
    if (config.locales) {
      this.vueComponent.kendoGridOptions.noRecords = {
        template: config.locales.pageable.messages.empty
      };
      this.vueComponent.translations = config.locales;
      if (
        this.vueComponent.kendoGridOptions.pageable &&
        typeof this.vueComponent.kendoGridOptions.pageable === "object"
      ) {
        this.vueComponent.kendoGridOptions.pageable.messages =
          config.locales.pageable.messages;
      }
      if (this.vueComponent.kendoGridOptions.filterable) {
        this.vueComponent.kendoGridOptions.filterable.messages =
          config.locales.filterable.messages;
      }
    }
  }

  /**
   * Compute the kendo grid options
   * @param {object} config - Grid configuration
   * @param {object|null} savedColsOpts - Saved columns configuration
   */
  prepareKendoGridOptions(config, savedColsOpts = {}) {
    this.vueComponent.kendoGridOptions.columns = [];
    /**
     * force to set visible input with value of real input filter
     * Use to view input in menu filter
     * @param e
     */
    this.vueComponent.kendoGridOptions.filterMenuOpen = e => {
      this.vueComponent.gridFilter.onfilterMenuOpen(e);
    };

    this.vueComponent.kendoGridOptions.filterMenuInit = e => {
      this.vueComponent.gridFilter.onfilterMenuInit(e);
    };

    this.vueComponent.kendoGridOptions.filter = e => {
      GridFilter.beforeFilterGrid(e);
    };

    this.prepareKendoGridLocales(config);
    this.prepareKendoGridPaging(config);
    this.prepareKendoGridToolbar(config);
    this.prepareKendoGridColumns(config);
    this.prepareKendoGridActions(config);

    if (
      savedColsOpts &&
      savedColsOpts.columns &&
      savedColsOpts.columns.length
    ) {
      this.vueComponent.kendoGridOptions.columns.forEach(col => {
        savedColsOpts.columns.forEach(savedCol => {
          if (col.field && savedCol.field && col.field === savedCol.field) {
            if (savedCol.width !== undefined) {
              col.width = savedCol.width;
            }
            if (savedCol.hidden !== undefined) {
              col.hidden = savedCol.hidden;
            }
          }
        });
      });
    }
  }

  /**
   * Create and initialize the kendo grid widget
   * @param config - Grid configuration
   * @param {object|null} savedColsOpts - Saved columns configuration
   */
  initKendoGrid(config, savedColsOpts = null) {
    this.createKendoWidget({
      columnHide: this.vueComponent.privateScope.notifyChange,
      columnShow: this.vueComponent.privateScope.notifyChange,
      columnReorder: this.vueComponent.privateScope.notifyChange,
      columnResize: this.vueComponent.privateScope.notifyChange,
      change: e => this.onGridSelectionChange(e),
      dataBound: e => {
        this.onGridDataBound(e);
        this.vueComponent.gridDataUtils.computeTotalExport(e.sender);
      }
    });

    this.prepareKendoGridOptions(config, savedColsOpts);

    this.vueComponent.kendoGrid.setOptions(this.vueComponent.kendoGridOptions);
    this.createDataSource(config, savedColsOpts);
  }

  onGridSelectionChange(event) {
    this.vueComponent.gridDataUtils.computeTotalExport(event.sender);
    if (
      (!this.vueComponent.isFullSelectionState &&
        this.vueComponent.kendoGrid.selectedKeyNames().length) ||
      (this.vueComponent.isFullSelectionState &&
        this.vueComponent.gridDataUtils.getUncheckRowsList().length)
    ) {
      this.vueComponent.kendoGrid.thead
        .find(".toggle-all-rows")
        .addClass("check-partial");
    } else {
      this.vueComponent.kendoGrid.thead
        .find(".toggle-all-rows")
        .removeClass("check-partial");
    }
  }

  /**
   * Create the kendo data source
   * @param {object} config - Grid configuration
   * @param {object|null} savedConfig - Saved columns configuration
   */
  createDataSource(config, savedConfig = null) {
    const contentURL = this.vueComponent.resolveContentUrl || config.contentURL;
    if (contentURL) {
      this.vueComponent.gridFilter.bindFilterEvents();
      this.vueComponent.kendoDataSourceOptions.transport = {
        read: options => this.readData(contentURL, options, config)
      };

      this.vueComponent.kendoDataSourceOptions.requestEnd = () => {
        this.vueComponent.gridFilter.refreshOperatorLabel();
      };
      if (config.smartFields) {
        this.vueComponent.kendoDataSourceOptions.schema.model = this.vueComponent.gridDataUtils.getDataSourceModel(
          config.smartFields
        );
      }
      this.vueComponent.kendoDataSourceOptions.schema.parse = response => {
        if (
          response &&
          response.data &&
          response.data.data &&
          response.data.data.smartElements
        ) {
          response.data.data.smartElements = this.vueComponent.gridDataUtils.parseData(
            response.data.data.smartElements
          );
        }
        return response;
      };
      if (savedConfig && savedConfig.pageSize) {
        this.vueComponent.kendoDataSourceOptions.pageSize =
          savedConfig.pageSize;
      }
      this.vueComponent.dataSource = new this.vueComponent.$kendo.data.DataSource(
        this.vueComponent.kendoDataSourceOptions
      );
    } else if (this.vueComponent.data && this.vueComponent.data.length) {
      this.vueComponent.dataSource = new this.vueComponent.$kendo.data.DataSource(
        {
          data: this.vueComponent.gridDataUtils.parseData(this.data)
        }
      );
    }
  }

  /**
   * Read content Kendo Data Source function
   * @param {string} contentURL - The content url
   * @param {object} kendoOptions - The kendo data options (pager, filter, sort...)
   * @param {object} config - Grid configuration
   */
  readData(contentURL, kendoOptions, config) {
    try {
      this.vueComponent.kendoReadOptionsData = kendoOptions.data;
      const event = new GridEvent(
        {
          url: contentURL,
          queryParams: this.vueComponent.privateScope.getQueryParamsData(
            config.smartFields,
            kendoOptions.data
          )
        },
        null,
        false
      );
      this.vueComponent.$emit("before-content-request", event);
      this.vueComponent.$http
        .get(event.data.url, {
          params: event.data.queryParams,
          paramsSerializer: params => this.vueComponent.$.param(params)
        })
        .then(response => {
          this.vueComponent.gridFilter.bindFilterEvents();
          if (
            response &&
            response.data &&
            response.data.data &&
            response.data.data.requestParameters &&
            response.data.data.requestParameters.pager
          ) {
            const pagerInfo = response.data.data.requestParameters.pager;
            if (
              pagerInfo &&
              pagerInfo.pageSize &&
              pagerInfo.pageSize !== this.vueComponent.dataSource.pageSize()
            ) {
              this.vueComponent.dataSource.pageSize(pagerInfo.pageSize);
            }
          }
          const responseEvent = new GridEvent(
            {
              content: response.data.data
            },
            null,
            false
          );
          this.vueComponent.$emit("after-content-response", responseEvent);
          response.data.data = responseEvent.data.content;
          kendoOptions.success(response);
        })
        .catch(err => {
          if (
            err.response &&
            err.response.data &&
            err.response.data.exceptionMessage
          ) {
            alert(err.response.data.exceptionMessage);
          }

          this.vueComponent.gridError.error(err);
          kendoOptions.error(err);
        });
    } catch (err) {
      // Handle general code exception and stop kendo datasource workflow
      this.vueComponent.gridError.error(err);
      kendoOptions.error(err);
    }
  }
}
