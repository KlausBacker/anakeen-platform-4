import { Vue } from "vue-property-decorator";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.columnmenu";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { TreeList, TreeListInstaller } from "@progress/kendo-treelist-vue-wrapper";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

Vue.use(DataSourceInstaller);
Vue.use(TreeListInstaller);

export default {
  name: "ss-treelist",
  mixins: [AnkI18NMixin],
  props: {
    messages: {
      type: String,
      default: () => ""
    },
    ssName: {
      type: String,
      default: () => ""
    },
    columnListKey: {
      type: String,
      default: () => ""
    },
    items: {
      type: Array,
      default: () => []
    },
    filterable: {
      default: () => ({
        extra: false,
        operators: { string: { contains: "Contains" } }
      })
    },
    sortable: {
      default: false
    },
    resizable: {
      default: true
    },
    url: {
      type: String,
      default: () => ""
    },
    getValues: {
      default: () => {
        return {};
      }
    },
    columnTemplate: {
      default: () => {
        return {};
      }
    },
    headerTemplate: {
      default: () => column => column.label || column.name
    },
    model: {
      default: () => ({
        id: "id",
        parentId: "parentId",
        expanded: true
      })
    },
    sort: {
      default: () => {
        return {};
      }
    },
    inlineFilters: {
      type: Boolean,
      default: false
    }
  },
  components: {
    "kendo-treelist": TreeList
  },
  watch: {
    url() {
      if (this.remoteDataSource) {
        this.remoteDataSource.read();
      }
    }
  },
  data() {
    return {
      remoteDataSource: "",
      columnSizeTab: window.localStorage.getItem("ss-list-column-size-conf-" + this.columnListKey)
        ? JSON.parse(window.localStorage.getItem("ss-list-column-size-conf-" + this.columnListKey))
        : [],
      filters: "",
      operatorconfig: "contains",
      operatorvalue: "contains",
      tmpMsgNoParam: this.messages
    };
  },
  computed: {
    msgNoParam: {
      get: function() {
        return this.tmpMsgNoParam;
      },
      set: function(newValue) {
        this.tmpMsgNoParam = newValue;
      }
    }
  },
  devCenterRefreshData() {
    if (this.remoteDataSource) {
      this.remoteDataSource.read();
    }
  },
  mounted() {
    this.remoteDataSource = new kendo.data.TreeListDataSource({
      transport: {
        read: options => {
          kendo.ui.progress(this.$(".k-grid-content", this.$el), true);
          this.$http
            .get(this.url)
            .then(response => {
              kendo.ui.progress(this.$(".k-grid-content", this.$el), false);
              options.success(response);
            })
            .catch(error => {
              kendo.ui.progress(this.$(".k-grid-content", this.$el), false);
              console.error(error);
              options.error(error);
            });
        }
      },
      schema: {
        data: response => {
          return this.getValues(response.data.data);
        },
        model: this.model
      },
      sort: this.sort
    });
    this.$nextTick(() => {
      if (this.inlineFilters) {
        this.createFilterRow();
      }
    });
    $(window).resize(() => {
      if (this.$refs.ssTreelist) {
        this.$refs.ssTreelist.kendoWidget().resize();
      }
    });
    const columns = window.localStorage.getItem("ss-list-column-conf-" + this.columnListKey);
    if (columns) {
      JSON.parse(columns).forEach(item => {
        if (item.hidden) {
          this.$refs.ssTreelist.kendoWidget().hideColumn(item.field);
        } else {
          this.$refs.ssTreelist.kendoWidget().showColumn(item.field);
        }
      });
    }
  },
  methods: {
    getmsgNoParam() {
      return this.msgNoParam;
    },
    onColumnResize(e) {
      e.preventDefault();
      let found = undefined;
      if (this.columnSizeTab.length > 0) {
        if (Array.isArray(e.column)) {
          found = this.columnSizeTab.find(item => item.field === e.column[0].field);
        } else {
          found = this.columnSizeTab.find(item => item.field === e.column.field);
        }
      }
      if (found) {
        found.width = e.newWidth;
      } else {
        if (Array.isArray(e.column)) {
          this.columnSizeTab.push({
            field: e.column[0].field,
            width: e.newWidth
          });
        } else {
          this.columnSizeTab.push({
            field: e.column.field,
            width: e.newWidth
          });
        }
      }
      return this.columnSizeTab;
    },
    onColumnHide(e) {
      window.localStorage.setItem(
        "ss-list-column-conf-" + this.columnListKey,
        JSON.stringify(this.$refs.ssTreelist.kendoWidget().columns)
      );
      this.filterShow(e.column.field, false);
    },
    onColumnShow(e) {
      window.localStorage.setItem(
        "ss-list-column-conf-" + this.columnListKey,
        JSON.stringify(this.$refs.ssTreelist.kendoWidget().columns)
      );
      this.filterShow(e.column.field);
    },
    onDataBound(e) {
      let tree = e.sender;
      this.removeRowClassName(tree);
      this.addRowClassName(tree);
      this.$emit("tree-list-data-bound", e);
    },
    onExpand(e) {
      let tree = e.sender;
      this.addRowClassName(tree);
    },
    onCollapse(e) {
      let tree = e.sender;
      this.addRowClassName(tree);
    },
    addRowClassName(tree) {
      let items = tree.items();
      window.setTimeout(() => {
        const that = this;
        items.each(function addTypeClass() {
          let dataItem = tree.dataItem(this);
          if (dataItem) {
            if (dataItem.type) {
              $(this).addClass(" attr-type--" + dataItem.type);
            }
            if (dataItem.structure !== that.ssName) {
              $(this).addClass(" is-herited");
            }
            if (dataItem.declaration === "overrided") {
              $(this).addClass(" is-overrided");
            }
            if (dataItem.parentId) {
              if (that.$refs.ssTreelist.kendoWidget().dataSource.get(dataItem.parentId).type === "array") {
                $(this).addClass(" is-array-children");
              }
            }
          }
        });
      }, 1);
    },
    removeRowClassName(tree) {
      let items = tree.items();
      window.setTimeout(() => {
        items.each(function removeTypeClass() {
          let dataItem = tree.dataItem(this);
          if (
            $(this)
              .attr("class")
              .includes(" attr-type--")
          ) {
            $(this).removeClass(" attr-type--" + dataItem.type);
          }
          if (
            $(this)
              .attr("class")
              .includes(" is-herited")
          ) {
            $(this).removeClass(" is-herited");
          }
          if (
            $(this)
              .attr("class")
              .includes(" is-overrided")
          ) {
            $(this).removeClass(" is-overrided");
          }
        });
      }, 1);
    },
    kendoWidget() {
      return this.$refs.ssTreelist.kendoWidget();
    },
    createFilterRow() {
      if (this.$refs.ssTreelist) {
        const tree = this.kendoWidget();
        const columns = tree.columns.filter(c => !c.hidden);
        columns.forEach(col => {
          switch (col.field) {
            case "config":
              this.filters += `<th class="k-header" data-field="${col.field}" data-title="${col.title}" role="columnheader" >
                <div class="filter-clearable" style="position:relative;">
                  <input class="k-textbox filter ${col.field}-filter" type="text"/>
                  <button class="filter-drop filter-drop-config">
                    <span class="k-icon k-i-filter"></span>
                  </button>
                </div>
              </th>`;
              this.filterRow(col.field);
              break;
            case "value":
              this.filters += `<th class="k-header" data-field="${col.field}" data-title="${col.title}" role="columnheader" >
                <div class="filter-clearable" style="position:relative;">
                  <input class="k-textbox filter ${col.field}-filter" type="text"/>
                  <button class="filter-drop filter-drop-value">
                    <span class="k-icon k-i-filter"></span>
                  </button>
                </div>
              </th>`;
              this.filterRow(col.field);
              break;
            default:
              this.filters += `<th class="k-header" data-field="${col.field}" data-title="${col.title}" role="columnheader" >
                <div class="filter-clearable" style="position:relative;">
                  <input class="k-textbox filter ${col.field}-filter" type="text"/>
                </div>
              </th>`;
              this.filterRow(col.field);
              break;
          }
        });
        tree.thead.append(`<tr role="row" class="filter-row">${this.filters}</tr>`);
        this.filterButton();
      }
    },
    filterShow(id, show = true) {
      if (show) {
        this.$(".filter-row", this.$el).append(`<th class="k-header" >
                <div class="filter-clearable" style="position:relative;">
                  <input class="k-textbox filter ${id}-filter" type="text"/>
                </div>
              </th>`);
      } else {
        this.$nextTick(() => {
          this.$(`.${id}-filter`, this.$el)
            .closest("th")
            .remove();
        });
      }
    },
    filterButton() {
      this.$(".filter-drop-config", this.$el).kendoButton({
        click: e => {
          let result = "";
          switch (this.operatorconfig) {
            case "contains":
              result = e.sender.element[0].firstElementChild.className.replace(/ k-i-filter/g, " k-i-filter-clear");
              e.sender.element[0].firstElementChild.className = result;
              this.operatorconfig = "isnotempty";
              e.sender.element[0].previousElementSibling.value = "is not empty:";
              this.$refs.ssTreelist.kendoWidget().dataSource.filter({
                field: "config",
                operator: item => {
                  return !item ? false : item.length > 0;
                }
              });
              break;
            case "isnotempty":
              result = e.sender.element[0].firstElementChild.className.replace(/ k-i-filter-clear/g, " k-i-filter");
              e.sender.element[0].firstElementChild.className = result;
              this.operatorconfig = "contains";
              e.sender.element[0].previousElementSibling.value = "";
              this.$refs.ssTreelist.kendoWidget().dataSource.filter({});
              break;
            default:
              break;
          }
        }
      });
      this.$(".filter-drop-value", this.$el).kendoButton({
        click: e => {
          let result = "";
          switch (this.operatorvalue) {
            case "contains":
              result = e.sender.element[0].firstElementChild.className.replace(/ k-i-filter/g, " k-i-filter-clear");
              e.sender.element[0].firstElementChild.className = result;
              this.operatorvalue = "isnotempty";
              e.sender.element[0].previousElementSibling.value = "is not empty:";
              this.$refs.ssTreelist.kendoWidget().dataSource.filter({
                field: "value",
                operator: item => {
                  return !item ? false : item.length > 0;
                }
              });
              break;
            case "isnotempty":
              result = e.sender.element[0].firstElementChild.className.replace(/ k-i-filter-clear/g, " k-i-filter");
              e.sender.element[0].firstElementChild.className = result;
              this.operatorvalue = "contains";
              e.sender.element[0].previousElementSibling.value = "";
              this.$refs.ssTreelist.kendoWidget().dataSource.filter({});
              break;
            default:
              break;
          }
        }
      });
    },
    filterRow(field) {
      this.$(this.$refs.ssTreelist.kendoWidget().thead, this.$el).on(
        "change",
        `[data-field=${field}] input.filter`,
        event => {
          let newFilter = {};
          let computedFilter = { filters: [], logic: "and" };
          let oldFilter = this.$refs.ssTreelist.kendoWidget().dataSource.filter() || {};
          let value = this.getFilterValue(event.currentTarget.value);
          if (value === "") {
            this.msgNoParam = this.messages;
          } else {
            this.msgNoParam = this.$t("There are no parameter value with this filter");
          }
          value = value.replace(" ", "");
          const colId = event.target.className.split(" ")[2].split("-")[0];
          if (value) {
            switch (colId) {
              case "config":
                event.target.value = value;
                newFilter = {
                  field: colId,
                  operator: "contains",
                  value: value
                };
                this.removeEmptyFilter(event);
                this.operatorconfig = "contains";
                break;
              case "value":
                event.target.value = value;
                newFilter = {
                  field: colId,
                  operator: (item, val) => {
                    if (!item) {
                      return false;
                    }
                    if (item instanceof String) {
                      return val.includes(item);
                    }
                    if (item instanceof Object) {
                      const realArray = item.toJSON();
                      if (Array.isArray(realArray)) {
                        let result = false;
                        let i = 0;
                        while (i < realArray.length && !result) {
                          const currentValue = realArray[i];
                          result = currentValue.indexOf(val) > -1;
                          i++;
                        }
                        return result;
                      }
                      return false;
                    }
                  },
                  value: value
                };
                this.removeEmptyFilter(event);
                this.operatorvalue = "contains";
                break;
              default:
                newFilter = {
                  field: colId,
                  operator: "contains",
                  value: value
                };
                break;
            }
            if (oldFilter.filters) {
              oldFilter.filters.forEach(filter => {
                if (filter.field === newFilter.field) {
                  computedFilter.filters = oldFilter.filters.filter(f => f.field !== newFilter.field);
                  oldFilter.filters = oldFilter.filters.filter(f => f.field !== newFilter.field);
                  computedFilter.filters = (oldFilter.filters || []).concat(newFilter);
                } else {
                  computedFilter.filters = (oldFilter.filters || []).concat(newFilter);
                }
              });
            } else {
              computedFilter.filters = (oldFilter.filters || []).concat(newFilter);
            }
          } else {
            computedFilter.filters = oldFilter.filters.filter(f => f.field !== colId);
          }
          this.$refs.ssTreelist.kendoWidget().dataSource.filter(computedFilter);
          this.$emit("filter", {
            sender: this.$refs.ssTreelist.kendoWidget(),
            filter: {
              field: colId,
              operator: "contains",
              value: value
            }
          });
        }
      );
    },
    removeEmptyFilter(event) {
      let result = "";
      result = event.target.nextElementSibling.firstElementChild.className.replace(/ k-i-filter-clear/g, " k-i-filter");
      event.target.nextElementSibling.firstElementChild.className = result;
    },
    getFilterValue(value) {
      if (value.match(/[\s\S]+:[\s\S]/g)) {
        return value.split(":")[1];
      } else if (value.match(/[\s\S]+:/g)) {
        return "";
      } else {
        return value;
      }
    },
    filter(filterObject) {
      this.$refs.ssTreelist.kendoWidget().dataSource.filter(filterObject);
    }
  }
};
