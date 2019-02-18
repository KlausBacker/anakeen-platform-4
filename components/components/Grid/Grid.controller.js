import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.toolbar";

import GridActions from "./utils/GridActions";
import GridEvent from "./utils/GridEvent";

import GridDataUtils from "./utils/GridDataUtils";
import GridFilter from "./utils/GridFilter";
import GridColumnsDialog from "./GridDialog/GridDialog";

import GridError from "./utils/GridError";
import GridProps from "./utils/GridProps";
import GridVueUtil from "./utils/GridVueUtil";
import GridKendoUtils from "./utils/GridKendoUtils";
const COMPLETE_FIELDS_INFO_URL = "/api/v2/grid/columns/<collection>";

export default {
  name: "ank-se-grid",
  components: {
    "grid-dialog": GridColumnsDialog
  },
  props: GridProps,

  watch: {
    urlConfig(newVal, oldVal) {
      if (newVal !== oldVal) {
        this.privateScope.initGrid();
      }
    },
    kendoDataSourceOptions(newVal) {
      this.dataSource = new this.$kendo.data.DataSource(newVal);
    },
    kendoGridOptions(newVal) {
      if (this.kendoGrid) {
        this.kendoGrid.setOptions(newVal);
      } else {
        this.$once("grid-ready", () => {
          this.kendoGrid.setOptions(newVal);
        });
      }
    },
    dataSource(newVal) {
      if (this.kendoGrid) {
        this.kendoGrid.setDataSource(newVal);
      } else {
        this.$once("grid-ready", () => {
          this.kendoGrid.setDataSource(newVal);
        });
      }
    }
  },

  created() {
    this.gridActions = new GridActions(this);
    this.gridFilter = new GridFilter(this);
    this.gridError = new GridError(this);
    this.gridDataUtils = new GridDataUtils(this);
    this.gridVueUtils = new GridVueUtil(this);
    this.gridKendoUtils = new GridKendoUtils(this);
    this.privateScope = {
      getQueryParamsData: (columns, kendoPagerInfo) => {
        const result = {};
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
                const queryParams = this.$.param({
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
        this.$kendo.ui.progress(this.$(this.$refs.gridWrapper), true);
        return this.privateScope
          .getGridConfig()
          .then(config => {
            this.gridConfig = config;
            this.gridKendoUtils.initKendoGrid(config, savedColsOpts);
            this.privateScope.bindGridEvents();
            this.$kendo.ui.progress(this.$(this.$refs.gridWrapper), false);
          })
          .catch(err => {
            this.gridError.error(err);
            this.$kendo.ui.progress(this.$(this.$refs.gridWrapper), false);
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
                this.$(this.$refs.kendoGrid)
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
  },
  computed: {
    isFullSelectionState() {
      return this.checkable && this.allRowsSelectable;
    },
    colsConfig() {
      if (this.kendoGrid) {
        return this.kendoGrid.columns;
      }
      return [];
    },

    resolveExportUrl() {
      const baseUrl = this.urlExport || "";
      if (baseUrl.indexOf("<collection>") > -1) {
        if (!this.collection) {
          console.warn("Grid config URL : You must provide a collection name");
          return "";
        }
      }
      const collection = this.collection;
      return baseUrl.replace("<collection>", collection.toString());
    },

    resolveConfigUrl() {
      const baseUrl = this.urlConfig || "";
      if (baseUrl.indexOf("<collection>") > -1) {
        if (!this.collection) {
          console.warn("Grid config URL : You must provide a collection name");
          return "";
        }
      }
      const collection = this.collection;
      return baseUrl.replace("<collection>", collection.toString());
    },
    resolveColumnsUrl() {
      const baseUrl = COMPLETE_FIELDS_INFO_URL;
      if (baseUrl.indexOf("<collection>") > -1) {
        if (!this.collection) {
          console.warn("Grid config URL : You must provide a collection name");
          return "";
        }
      }
      const collection = this.collection;
      return baseUrl.replace("<collection>", collection.toString());
    },
    resolveContentUrl() {
      const baseUrl = this.urlContent;
      if (baseUrl.indexOf("<collection>") > -1) {
        if (!this.collection) {
          console.warn("Grid content URL : You must provide a collection name");
          return "";
        }
      }
      const collection = this.collection;
      return baseUrl.replace("<collection>", collection.toString());
    },
    translations() {
      return {
        emptyMessage: this.$pgettext("SEGrid", "No element on this page"),
        itemsPerPage: this.$pgettext("SEGrid", "items per page"),
        contains: this.$pgettext("SEGrid", "Contains"),
        doesNotContain: this.$pgettext("SEGrid", "Does not contain"),
        isEmpty: this.$pgettext("SEGrid", "Is empty"),
        isNotEmpty: this.$pgettext("SEGrid", "Is not empty"),
        selectOperator: this.$pgettext(
          "SEGrid",
          "-- Select another operator --"
        ),
        extraOperator: this.$pgettext("SEGrid", "Extra operators..."),
        columns: this.$pgettext("SEGrid", "Grid Settings"),
        consult: this.$pgettext("SEGrid", "Consult"),
        custom: this.$pgettext("SEGrid", "Custom"),
        edit: this.$pgettext("SEGrid", "Edit"),
        export: this.$pgettext("SEGrid", "Export as XSLX")
      };
    }
  },
  mounted() {
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
          const $header = this.$(e.currentTarget.data("th"));
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
          const $header = this.$(e.currentTarget);
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
  },
  data() {
    return {
      allRowsSelectable: false,
      uncheckRows: {},
      dataSource: [],
      kendoGrid: null,
      gridConfig: null,
      kendoDataSourceOptions: {
        serverPaging: this.serverPaging,
        serverFiltering: this.serverFiltering,
        serverSorting: this.serverSorting,
        pageSize:
          this.pageSizes && this.pageSizes.length ? this.pageSizes[0] : 10,
        schema: {
          data: response => response.data.data.smartElements,
          total: response => response.data.data.requestParameters.pager.total
        }
      },
      kendoGridOptions: {
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
      }
    };
  },
  methods: {
    setKendoOptions(options) {
      this.kendoGridOptions = options;
    },
    setData(data) {
      this.dataSource = new this.$kendo.data.DataSource({
        data: this.gridDataUtils.parseData(data)
      });
    },
    onSettingsChange(changes) {
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
};
