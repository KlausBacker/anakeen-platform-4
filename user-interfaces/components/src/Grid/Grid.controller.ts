import Vue from "vue";

import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid";
import { Component, Prop, Watch } from "vue-property-decorator";

import GridExpandButton from "./Components/GridExpandButton/GridExpandButton.vue";
import GridPager from "./Components/GridPager/GridPager.vue";

import GridActions from "./utils/GridActions";
import GridEvent from "./utils/GridEvent";

import GridDataUtils from "./utils/GridDataUtils";
import GridFilter from "./utils/GridFilter";

import GridError from "./utils/GridError";
import GridKendoUtils from "./utils/GridKendoUtils";
import GridVueUtil from "./utils/GridVueUtil";
const COMPLETE_FIELDS_INFO_URL = "/api/v2/grid/columns/<collection>";
import VueSetup from "../setup.js";
// eslint-disable-next-line no-unused-vars
import { IGrid } from "./IGrid";
Vue.use(VueSetup);

@Component({
  components: {
    "ank-se-grid-expand-button": GridExpandButton,
    "ank-se-grid-pager": GridPager
  },
  name: "ank-se-grid"
})
export default class GridController extends Vue {
  public get isFullSelectionState() {
    return this.checkable && this.allRowsSelectable;
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
  @Prop({
    default: "",
    type: String
  })
  public persistStateKey: string;
  @Prop({
    default: true,
    type: Boolean
  })
  public contextTitles;

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
  public contextTitlesSeparator;

  @Prop({
    default: "/api/v2/grid/config/<collection>",
    type: String
  })
  public urlConfig;
  @Prop({
    default: "/api/v2/grid/export/<transaction>/<collection>",
    type: String
  })
  public urlExport;
  @Prop({
    default: "/api/v2/grid/content/<collection>",
    type: String
  })
  public urlContent;
  @Prop({
    default: "",
    type: String
  })
  public collection;
  @Prop({
    default: "",
    type: String
  })
  public emptyCell;
  @Prop({
    default: "multiple",
    type: String
  })
  public sortable;
  @Prop({
    default: true,
    type: Boolean
  })
  public serverSorting;
  @Prop({
    default: "menu",
    type: String
  })
  public filterable;
  @Prop({
    default: false,
    type: Boolean
  })
  public refresh;
  @Prop({
    default: false,
    type: Boolean
  })
  public reorderable;
  @Prop({
    default: true,
    type: Boolean
  })
  public serverFiltering;
  @Prop({
    default: true,
    type: Boolean
  })
  public pageable;
  @Prop({
    default: () => [10, 20, 50],
    type: [Boolean, Array]
  })
  public pageSizes;
  @Prop({
    default: true,
    type: Boolean
  })
  public serverPaging;
  @Prop({
    default: true,
    type: Boolean
  })
  public resizable;
  @Prop({
    default: false,
    type: [Boolean, String]
  })
  public selectable;
  @Prop({
    default: false,
    type: Boolean
  })
  public checkable;

  @Prop({
    default: true,
    type: Boolean
  })
  public persistSelection;
  public translations = {
    uploadAllResults: "Upload all results",
    uploadReport: "upload",
    uploadSelection: "Upload selected items"
  };
  public collectionProperties = {};

  public privateScope: IGrid;

  public $refs!: {
    kendoGrid: HTMLElement;
    gridWrapper: HTMLElement;
  };
  public gridActions: any = null;
  public gridDataUtils: any = null;
  public gridError: any = null;
  public gridFilter: any = null;
  public gridInstance: any = null;
  public gridVueUtils: any = null;
  public gridKendoUtils: any = null;
  public allRowsSelectable: boolean = false;
  public uncheckRows: object = {};
  public dataSource: any = null;
  public kendoGrid: any = null;
  public gridConfig: any = null;
  public kendoDataSourceOptions: object = {
    pageSize: this.pageSizes && this.pageSizes.length ? this.pageSizes[0] : 10,
    schema: {
      data: response => response.data.data.smartElements,
      total: response => response.data.data.requestParameters.pager.total
    },
    serverFiltering: this.serverFiltering,
    serverPaging: this.serverPaging,
    serverSorting: this.serverSorting
  };
  public kendoGridOptions: object = {
    filterable: this.filterable
      ? {
          mode: this.filterable === "inline" ? "menu, row" : this.filterable
        }
      : this.filterable,
    pageable: this.pageable
      ? {
          numeric: false,
          pageSizes: this.pageSizes === true ? [10, 20, 50] : this.pageSizes,
          refresh: this.refresh
        }
      : this.pageable,
    persistSelection: this.persistSelection,
    reorderable: this.reorderable,
    resizable: this.resizable,
    selectable: this.selectable,
    sortable: this.sortable
      ? {
          mode: this.sortable,
          showIndexes: true
        }
      : this.sortable
  };
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
  public created() {
    this.gridActions = new GridActions(this);
    this.gridFilter = new GridFilter(this);
    this.gridError = new GridError(this);
    this.gridDataUtils = new GridDataUtils(this);
    this.gridVueUtils = new GridVueUtil(this);
    this.gridKendoUtils = new GridKendoUtils(this);
    this.privateScope = {
      getQueryParamsData: (columns, kendoPagerInfo) => {
        const result = { abstractFields: [], fields: [] };
        const defaultValues = { take: "all" };
        if (kendoPagerInfo) {
          Object.keys(kendoPagerInfo).forEach(key => {
            if (kendoPagerInfo[key] !== undefined) {
              result[key] = kendoPagerInfo[key];
            } else {
              result[key] = defaultValues[key];
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

          const abstracts = columns
            .filter(c => c.abstract)
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
          if (abstracts) {
            result.abstractFields = abstracts;
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
                    const serverConfig = response.data.data.fields || [];
                    serverConfig.forEach(config => {
                      const field = config.field;
                      const clientConfIndex = clientConfig.smartFields.findIndex(c => c.field === field);
                      if (clientConfIndex > -1) {
                        clientConfig.smartFields[clientConfIndex] = Object.assign(
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
              true // Cancelable
            );
            this.$emit("before-config-request", event);

            if (!event.isDefaultPrevented()) {
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
              reject("Grid config: configuration has not been fetched");
            }
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
            kendo.ui.progress(kendo.jQuery(this.$refs.gridWrapper), false);
          })
          .catch(err => {
            this.gridError.error(err);
            kendo.ui.progress(kendo.jQuery(this.$refs.gridWrapper), false);
          });
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
          if ($header.data("id") === "ank-se-grid-actions" || $header.data("id") === "ank-se-grid-checkable") {
            e.preventDefault();
          }
        });
      }

      if (this.reorderable) {
        this.kendoGrid._draggableInstance.bind("dragstart", e => {
          const $header = $(e.currentTarget);
          this.kendoGrid._draggableInstance.options.autoScroll = true;
          if ($header.data("id") === "ank-se-grid-actions" || $header.data("id") === "ank-se-grid-checkable") {
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
      this.gridInstance = this;
      this.$emit("grid-ready");
    });
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

  public onExpandButtonClicked() {
    $(this.kendoGrid.element).toggleClass("grid-row-collapsed");
  }
}
