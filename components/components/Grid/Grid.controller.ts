import Vue from "vue";
import { Component, Watch, Prop } from "vue-property-decorator";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.toolbar";

import GridActions from "./utils/GridActions";
import GridEvent from "./utils/GridEvent";

import GridDataUtils from "./utils/GridDataUtils";
import GridFilter from "./utils/GridFilter";
const GridColumnsDialog = () => import("./GridDialog/GridDialog.vue");

import GridError from "./utils/GridError";
import GridProps from "./utils/GridProps";
import GridVueUtil from "./utils/GridVueUtil";
import GridKendoUtils from "./utils/GridKendoUtils";
const COMPLETE_FIELDS_INFO_URL = "/api/v2/grid/columns/<collection>";
import { IGrid } from "./IGrid";

declare var kendo;

@Component({
  name: "ank-se-grid",
  components: {
    "grid-dialog": GridColumnsDialog
  },
  props: GridProps
})
export default class GridController extends Vue {
  @Watch("urlConfig")
  public watchUrlConfig(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.ps.privateScope.initGrid();
    }
  }
  @Watch("kendoDataSourceOptions")
  public watchKendoDataSourceOptions(newVal) {
    this.dataSource = new kendo.data.DataSource(newVal);
  }
  @Watch("kendoGridOptions")
  public watchKendoGridOptions(newVal) {
    if (this.kendoGrid) {
      this.kendoGrid.setOptions(newVal);
    } else {
      this.$once("grid-ready", () => {
        this.kendoGrid.setOptions(newVal);
      });
    }
  }
  @Watch("dataSource")
  public watchDataSource(newVal) {
    if (this.kendoGrid) {
      this.kendoGrid.setDataSource(newVal);
    } else {
      this.$once("grid-ready", () => {
        this.kendoGrid.setDataSource(newVal);
      });
    }
  }

  public created() {
    this.gridActions = new GridActions(this);
    this.gridFilter = new GridFilter(this);
    this.gridError = new GridError(this);
    this.gridDataUtils = new GridDataUtils(this);
    this.gridVueUtils = new GridVueUtil(this);
    this.gridKendoUtils = new GridKendoUtils(this);
    this.ps.privateScope = {
      getQueryParamsData: (columns, kendoPagerInfo) => {
        const result = {fields: []};
        if (kendoPagerInfo) {
          Object.keys(kendoPagerInfo).forEach(key => {
            if (kendoPagerInfo[key] !== undefined) {
              result[key] = kendoPagerInfo[key];
            }
          });
        }
        if (columns && columns.length) {
          const fields = columns
            .filter(c => !c.abstract)
            .map(fieldConfig => {
              if (fieldConfig.property) {
                return `document.properties.${fieldConfig.field}`;
              }
              return `document.attributes.${fieldConfig.field}`;
            })
            .join(",");
          if (fields) {
            result.fields = fields;
          }
        }
        return result;
      },

      getGridConfig: () => {
        return new Promise((resolve, reject) => {
          const clientConfig = this.gridVueUtils.getSlotConfig();
          if (clientConfig) {
            if (clientConfig.smartFields && clientConfig.smartFields.length) {
              if (this.resolveColumnsUrl) {
                const requestedFields = clientConfig.smartFields
                  .filter(f => f.field)
                  .map(f => f.field)
                  .join(",");
                const queryParams = $.param({
                  fields: requestedFields
                });
                this.$http
                  .get(`${this.resolveColumnsUrl}?${queryParams}`)
                  .then(response => {
                    const serverConfig = response.data.data || [];
                    serverConfig.forEach(config => {
                      const field = config.field;
                      const clientConfIndex = clientConfig.smartFields.findIndex(
                        c => c.field === field
                      );
                      if (clientConfIndex > -1) {
                        clientConfig.smartFields[
                          clientConfIndex
                        ] = Object.assign(
                          {},
                          config,
                          clientConfig.smartFields[clientConfIndex]
                        );
                      }
                    });
                    resolve(clientConfig);
                  })
                  .catch(err => {
                    this.gridError.error(err);
                    reject(err);
                  });
              } else {
                resolve(clientConfig);
              }
            } else {
              resolve(clientConfig);
            }
          } else if (this.resolveConfigUrl) {
            const event = new GridEvent(
              {
                url: this.resolveConfigUrl
              },
              null,
              false
            );
            this.$emit("before-config-request", event);
            this.$http
              .get(event.data.url)
              .then(response => {
                const config = response.data.data;
                const responseEvent = new GridEvent(
                  {
                    config
                  },
                  null,
                  false
                );
                this.$emit("after-config-response", responseEvent);
                resolve(responseEvent.data.config);
              })
              .catch(err => {
                this.gridError.error(err);
                reject(err);
              });
          } else {
            reject("Grid config: no config is provided");
          }
        });
      },

      initGrid: (savedColsOpts = null) => {
        kendo.ui.progress(kendo.jquery(this.$refs.gridWrapper), true);
        return this.ps.privateScope
          .getGridConfig()
          .then(config => {
            this.gridConfig = config;
            this.gridKendoUtils.initKendoGrid(config, savedColsOpts);
            this.ps.privateScope.bindGridEvents();
            kendo.ui.progress(kendo.jquery(this.$refs.gridWrapper), false);
          })
          .catch(err => {
            this.gridError.error(err);
            kendo.ui.progress(kendo.jquery(this.$refs.gridWrapper), false);
          });
      },

      bindGridEvents: () => {
        if (this.kendoGrid) {
          if (
            this.gridConfig &&
            this.gridConfig.toolbar &&
            this.gridConfig.toolbar.actionConfigs
          ) {
            this.gridConfig.toolbar.actionConfigs.forEach(conf => {
              if (conf.action !== "export") {
                const action = this.gridActions.getToolbarAction(conf.action);
                kendo.jquery(this.$refs.kendoGrid)
                  .find(".k-grid-toolbar")
                  .on("click", `.grid-toolbar-${conf.action}-action`, e =>
                    action.click(e, conf.action)
                  );
              }
            });
          }
        }
      },

      notifyChange: () => {
        const event = new GridEvent(
          {
            grid: this
          },
          this.$el,
          true,
          "GridStateEvent"
        );
        this.$emit("before-save-state", event);
        if (!event.isDefaultPrevented() && GridProps.persistStateKey) {
          localStorage.setItem(
            GridProps.persistStateKey,
            kendo.stringify({
              columns: this.kendoGrid.getOptions().columns.map(c => ({
                field: c.field,
                hidden: c.hidden,
                width: c.width
              })),
              pageSize: this.dataSource.pageSize() || 10
            })
          );
        }
      }
    };
  }
  public get isFullSelectionState() {
    return GridProps.checkable && this.allRowsSelectable;
  }
  public get colsConfig() {
    if (this.kendoGrid) {
      return this.kendoGrid.columns;
    }
    return [];
  }

  public get resolveExportUrl() {
    const baseUrl = GridProps.urlExport || "";
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!GridProps.collection) {
        console.warn("Grid config URL : You must provide a collection name");
        return "";
      }
    }
    const collection = GridProps.collection;
    return baseUrl.replace("<collection>", collection.toString());
  }

  public get resolveConfigUrl() {
    const baseUrl = GridProps.urlConfig || "";
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!GridProps.collection) {
        console.warn("Grid config URL : You must provide a collection name");
        return "";
      }
    }
    const collection = GridProps.collection;
    return baseUrl.replace("<collection>", collection.toString());
  }
  public get resolveColumnsUrl() {
    const baseUrl = COMPLETE_FIELDS_INFO_URL;
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!GridProps.collection) {
        console.warn("Grid config URL : You must provide a collection name");
        return "";
      }
    }
    const collection = GridProps.collection;
    return baseUrl.replace("<collection>", collection.toString());
  }
  public get resolveContentUrl() {
    const baseUrl = GridProps.urlContent;
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!GridProps.collection) {
        console.warn("Grid content URL : You must provide a collection name");
        return "";
      }
    }
    const collection = GridProps.collection;
    return baseUrl.replace("<collection>", collection.toString());
  }
  public get translations() {
    return {
      emptyMessage: this.$pgettext("SEGrid", "No element on this page"),
      itemsPerPage: this.$pgettext("SEGrid", "items per page"),
      contains: this.$pgettext("SEGrid", "Contains"),
      doesNotContain: this.$pgettext("SEGrid", "Does not contain"),
      isEmpty: this.$pgettext("SEGrid", "Is empty"),
      isNotEmpty: this.$pgettext("SEGrid", "Is not empty"),
      selectOperator: this.$pgettext("SEGrid", "-- Select another operator --"),
      extraOperator: this.$pgettext("SEGrid", "Extra operators..."),
      columns: this.$pgettext("SEGrid", "Grid Settings"),
      consult: this.$pgettext("SEGrid", "Consult"),
      custom: this.$pgettext("SEGrid", "Custom"),
      edit: this.$pgettext("SEGrid", "Edit"),
      export: this.$pgettext("SEGrid", "Export as XSLX")
    };
  }
  public mounted() {
    let saveColumnsOptions = null;
    if (GridProps.persistStateKey) {
      saveColumnsOptions = localStorage.getItem(GridProps.persistStateKey);
      if (saveColumnsOptions) {
        saveColumnsOptions = JSON.parse(saveColumnsOptions);
      }
    }
    this.ps.privateScope.initGrid(saveColumnsOptions).then(() => {
      if (GridProps.resizable) {
        this.kendoGrid.resizable.bind("start", e => {
          const $header = $(e.currentTarget.data("th"));
          if (
            $header.data("id") === "ank-se-grid-actions" ||
            $header.data("id") === "ank-se-grid-checkable"
          ) {
            e.preventDefault();
          }
        });
      }

      if (GridProps.reorderable) {
        this.kendoGrid._draggableInstance.bind("dragstart", e => {
          const $header = $(e.currentTarget);
          this.kendoGrid._draggableInstance.options.autoScroll = true;
          if (
            $header.data("id") === "ank-se-grid-actions" ||
            $header.data("id") === "ank-se-grid-checkable"
          ) {
            e.preventDefault();
          }
        });
        this.kendoGrid._draggableInstance.bind("drag", e => {
          if (e.dropTarget && e.dropTarget.element) {
            const $dropHeader = e.dropTarget.element;
            if (
              $dropHeader.data("id") === "ank-se-grid-actions" ||
              $dropHeader.data("id") === "ank-se-grid-checkable"
            ) {
              e.preventDefault();
              this.kendoGrid._draggableInstance.options.autoScroll = false;
              this.kendoGrid._draggableInstance._cancel();
            }
          }
        });
      }
      this.$emit("grid-ready");
    });
  }
  public ps : IGrid;
  public gridActions: any;
  public gridFilter: any;
  public gridError: any;
  public gridDataUtils: any;
  public gridVueUtils: any;
  public gridKendoUtils: any;
  public allRowsSelectable: boolean = false;
  public uncheckRows: object = {};
  public dataSource: any;
  public kendoGrid: any = null;
  public gridConfig: any = null;
  public kendoDataSourceOptions: object = {
    serverPaging: GridProps.serverPaging,
    serverFiltering: GridProps.serverFiltering,
    serverSorting: GridProps.serverSorting,
    pageSize: GridProps.pageSizes && GridProps.pageSizes.length ? GridProps.pageSizes[0] : 10,
    schema: {
      data: response => response.data.data.smartElements,
      total: response => response.data.data.requestParameters.pager.total
    }
  };
  public kendoGridOptions: object = {
    filterable: GridProps.filterable
      ? {
          mode: GridProps.filterable === "inline" ? "menu, row" : GridProps.filterable
        }
      : GridProps.filterable,
    sortable: GridProps.sortable
      ? {
          mode: GridProps.sortable,
          showIndexes: true
        }
      : GridProps.sortable,
    reorderable: GridProps.reorderable,
    pageable: GridProps.pageable
      ? {
          pageSizes: GridProps.pageSizes,
          numeric: false,
          messages: {
            itemsPerPage: "résultats par page",
            of: "sur",
            display: "{0} - {1} sur {2} résultats"
          }
        }
      : GridProps.pageable,
    resizable: GridProps.resizable,
    selectable: GridProps.selectable,
    persistSelection: GridProps.persistSelection
  };
  public setKendoOptions(options) {
    this.kendoGridOptions = options;
  }
  public setData(data) {
    this.dataSource = new kendo.data.DataSource({
      data: this.gridDataUtils.parseData(data)
    });
  }
  public onSettingsChange(changes) {
    if (changes) {
      Object.keys(changes).forEach(colId => {
        if (this.kendoGrid) {
          if (changes[colId].display === true) {
            this.kendoGrid.showColumn(colId);
          } else if (changes[colId].display === false) {
            this.kendoGrid.hideColumn(colId);
          }
        }
      });
    }
  }
}
