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
import GridVueUtil from "./utils/GridVueUtil";
import GridKendoUtils from "./utils/GridKendoUtils";
const COMPLETE_FIELDS_INFO_URL = "/api/v2/grid/columns/<collection>";
import { IGrid } from "./IGrid";
import VueSetup from "../setup.js";
Vue.use(VueSetup);

@Component({
  name: "ank-se-grid",
  components: {
    "grid-dialog": GridColumnsDialog
  }
})
export default class GridController extends Vue {
  @Prop({
    type: String,
    default: ""
  })
  public persistStateKey: string;
  @Prop({
    type: Boolean,
    default: true
  })
  public contextTitles;
  @Prop({
    type: String,
    default: "-"
  })
  public contextTitlesSeparator;
  @Prop({
    type: String,
    default: "/api/v2/grid/config/<collection>"
  })
  public urlConfig;
  @Prop({
    type: String,
    default: "/api/v2/grid/export/<transaction>/<collection>"
  })
  public urlExport;
  @Prop({
    type: String,
    default: "/api/v2/grid/content/<collection>"
  })
  public urlContent;
  @Prop({
    type: String,
    default: ""
  })
  public collection;
  @Prop({
    type: String,
    default: ""
  })
  public emptyCell;
  @Prop({
    type: String,
    default: "N/A"
  })
  public notExistValue;
  @Prop({
    type: String,
    default: "multiple"
  })
  public sortable;
  @Prop({
    type: Boolean,
    default: true
  })
  public serverSorting;
  @Prop({
    type: String,
    default: "menu"
  })
  public filterable;
  @Prop({
    type: Boolean,
    default: false
  })
  public reorderable;
  @Prop({
    type: Boolean,
    default: true
  })
  public serverFiltering;
  @Prop({
    type: Boolean,
    default: true
  })
  public pageable;
  @Prop({
    type: [Boolean, Array],
    default: () => [10, 20, 50]
  })
  public pageSizes;
  @Prop({
    type: Boolean,
    default: true
  })
  public serverPaging;
  @Prop({
    type: Boolean,
    default: true
  })
  public resizable;
  @Prop({
    type: Array,
    default: () => []
  })
  public data;
  @Prop({
    type: [Boolean, String],
    default: false
  })
  public selectable;
  @Prop({
    type: Boolean,
    default: false
  })
  public checkable;

  @Prop({
    type: Boolean,
    default: true
  })
  public persistSelection;

  public privateScope: IGrid;
  @Watch("urlConfig")
  public watchUrlConfig(newVal, oldVal) {
    if (newVal !== oldVal) {
      this.privateScope.initGrid();
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

  public $refs!: {
    kendoGrid: HTMLElement;
    gridWrapper: HTMLElement;
  };

  public created() {
    this.gridActions = new GridActions(this);
    this.gridFilter = new GridFilter(this);
    this.gridError = new GridError(this);
    this.gridDataUtils = new GridDataUtils(this);
    this.gridVueUtils = new GridVueUtil(this);
    this.gridKendoUtils = new GridKendoUtils(this);
    this.privateScope = {
      getQueryParamsData: (columns, kendoPagerInfo) => {
        const result = { fields: [] };
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
        kendo.ui.progress(kendo.jQuery(this.$refs.gridWrapper), true);
        return this.privateScope
          .getGridConfig()
          .then(config => {
            this.gridConfig = config;
            this.gridKendoUtils.initKendoGrid(config, savedColsOpts);
            this.privateScope.bindGridEvents();
            kendo.ui.progress(kendo.jQuery(this.$refs.gridWrapper), false);
          })
          .catch(err => {
            this.gridError.error(err);
            kendo.ui.progress(kendo.jQuery(this.$refs.gridWrapper), false);
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
                kendo
                  .jQuery(this.$refs.kendoGrid)
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
        if (!event.isDefaultPrevented() && this.persistStateKey) {
          localStorage.setItem(
            this.persistStateKey,
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
    return this.checkable && this.allRowsSelectable;
  }
  public get colsConfig() {
    if (this.kendoGrid) {
      return this.kendoGrid.columns;
    }
    return [];
  }

  public get resolveExportUrl() {
    const baseUrl = this.urlExport || "";
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!this.collection) {
        console.warn("Grid config URL : You must provide a collection name");
        return "";
      }
    }
    const collection = this.collection;
    return baseUrl.replace("<collection>", collection.toString());
  }

  public get resolveConfigUrl() {
    const baseUrl = this.urlConfig || "";
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!this.collection) {
        console.warn("Grid config URL : You must provide a collection name");
        return "";
      }
    }
    const collection = this.collection;
    return baseUrl.replace("<collection>", collection.toString());
  }
  public get resolveColumnsUrl() {
    const baseUrl = COMPLETE_FIELDS_INFO_URL;
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!this.collection) {
        console.warn("Grid config URL : You must provide a collection name");
        return "";
      }
    }
    const collection = this.collection;
    return baseUrl.replace("<collection>", collection.toString());
  }
  public get resolveContentUrl() {
    const baseUrl = this.urlContent;
    if (baseUrl.indexOf("<collection>") > -1) {
      if (!this.collection) {
        console.warn("Grid content URL : You must provide a collection name");
        return "";
      }
    }
    const collection = this.collection;
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
    if (this.persistStateKey) {
      saveColumnsOptions = localStorage.getItem(this.persistStateKey);
      if (saveColumnsOptions) {
        saveColumnsOptions = JSON.parse(saveColumnsOptions);
      }
    }
    this.privateScope.initGrid(saveColumnsOptions).then(() => {
      if (this.resizable) {
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

      if (this.reorderable) {
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
  public gridActions: any = null;
  public gridFilter: any = null;
  public gridError: any = null;
  public gridDataUtils: any = null;
  public gridVueUtils: any = null;
  public gridKendoUtils: any = null;
  public allRowsSelectable: boolean = false;
  public uncheckRows: object = {};
  public dataSource: any = null;
  public kendoGrid: any = null;
  public gridConfig: any = null;
  public kendoDataSourceOptions: object = {
    serverPaging: this.serverPaging,
    serverFiltering: this.serverFiltering,
    serverSorting: this.serverSorting,
    pageSize: this.pageSizes && this.pageSizes.length ? this.pageSizes[0] : 10,
    schema: {
      data: response => response.data.data.smartElements,
      total: response => response.data.data.requestParameters.pager.total
    }
  };
  public kendoGridOptions: object = {
    filterable: this.filterable
      ? {
          mode: this.filterable === "inline" ? "menu, row" : this.filterable
        }
      : this.filterable,
    sortable: this.sortable
      ? {
          mode: this.sortable,
          showIndexes: true
        }
      : this.sortable,
    reorderable: this.reorderable,
    pageable: this.pageable
      ? {
          pageSizes: this.pageSizes,
          numeric: false,
          messages: {
            itemsPerPage: "résultats par page",
            of: "sur",
            display: "{0} - {1} sur {2} résultats"
          }
        }
      : this.pageable,
    resizable: this.resizable,
    selectable: this.selectable,
    persistSelection: this.persistSelection
  };
  public setKendoOptions(options) {
    this.kendoGridOptions = options;
  }
  public setData(data) {
    this.dataSource = new kendo.data.DataSource({
      data: this.gridDataUtils.parseData(data)
    });
    console.log(this.dataSource);
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
