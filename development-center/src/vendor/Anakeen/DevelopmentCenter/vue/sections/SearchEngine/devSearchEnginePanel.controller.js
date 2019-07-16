import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.numerictextbox";
export default {
  name: "ank-dev-search-panel",
  props: {
    value: {
      inputvalue: "",
      fromname: "",
      id: "",
      name: "",
      title: ""
    },
    url: {
      type: String,
      default: "/api/v2/devel/ui/search/"
    }
  },
  data() {
    return {
      grid: null,
      filters: {
        inputvalue: "",
        fromname: "",
        id: "",
        name: "",
        title: ""
      },
      fields: ["fromname", "id", "name", "title"],
      dataSource: new kendo.data.DataSource({
        schema: {
          data: response => {
            return response.data.data.data;
          },
          model: {
            fields: {
              inputvalue: {
                type: "string"
              },
              fromname: {
                type: "string"
              },
              id: {
                type: "number"
              },
              name: {
                type: "string"
              },
              title: {
                type: "string"
              }
            }
          },
          total: response => {
            return response.data.data.total;
          }
        },
        serverFiltering: true,
        serverPaging: true,
        pageSize: 50,
        transport: {
          read: options => {
            this.$http
              .get(this.url, {
                params: options.data,
                paramsSerializer: kendo.jQuery.param
              })
              .then(response => {
                options.success(response);
              })
              .catch(error => {
                options.error(error);
              });
          }
        }
      })
    };
  },
  computed: {
    inputValue: function() {
      let value = "";
      if (this.filters && this.filters.inputvalue) {
        value = this.filters.inputvalue;
      }
      return value;
    }
  },
  methods: {
    onEnterCb() {
      this.filters.inputvalue = this.$refs.searchInput.value;
    },
    updateGridFilters() {
      const filtersToApply = [];

      if (this.filters) {
        filtersToApply.push({
          field: "inputvalue",
          value: this.filters.inputvalue
        });

        if (this.filters.name) {
          filtersToApply.push({
            field: "name",
            value: this.filters.name
          });
        }
        if (this.filters.id) {
          filtersToApply.push({
            field: "id",
            value: this.filters.id
          });
        }
        if (this.filters.fromname) {
          filtersToApply.push({
            field: "fromname",
            value: this.filters.fromname
          });
        }
        if (this.filters.title) {
          filtersToApply.push({
            field: "title",
            value: this.filters.title
          });
        }
      }

      this.dataSource.filter(filtersToApply);
    },
    updateFiltersValue() {
      this.filters.inputvalue = this.$refs.searchInput.value;
      const gridFilters = this.dataSource.filter().filters;
      let fields = [...this.fields];
      gridFilters.forEach(gridFilter => {
        const field = gridFilter.field;
        const fieldValue = gridFilter.value;
        if (fields.includes(field)) {
          this.filters[field] = fieldValue;
          fields.splice(fields.indexOf(field), 1);
        }
      });
      fields.forEach(fieldNotSet => {
        this.filters[fieldNotSet] = "";
      });
    }
  },
  created() {
    this.$_hubEventBus.on("searchEngineClick", () => {
      setTimeout(() => {
        this.$refs.searchInput.focus();
      }, 300);
    });
  },
  mounted() {
    this.grid = $(this.$refs.grid)
      .kendoGrid({
        dataSource: this.dataSource,
        dataBound: () => {
          $(".search-engine-link").each(function() {
            $(this).kendoButton();
          });
          this.updateFiltersValue();
        },
        filter: () => {
          // this.filters.inputvalue = this.$refs.searchInput.value;
        },
        pageable: {
          pageSizes: [20, 50, 100]
        },
        filterable: {
          mode: "menu, row"
        },
        resizable: true,
        columns: [
          {
            field: "inputvalue",
            hidden: true
          },
          {
            field: "fromname",
            title: "Smart Structure",
            width: 300,
            minResizableWidth: 50,
            filterable: {
              cell: {
                delay: 9999999999
              }
            }
          },
          {
            field: "id",
            title: "Id",
            width: 200,
            minResizableWidth: 50,
            filterable: {
              cell: {
                delay: 9999999999,
                template: function(args) {
                  args.element.kendoNumericTextBox({
                    spinners: false,
                    decimal: 0,
                    format: "n0"
                  });
                }
              }
            }
          },
          {
            field: "name",
            title: "Name",
            width: 300,
            minResizableWidth: 50,
            filterable: {
              cell: {
                delay: 9999999999
              }
            }
          },
          {
            field: "title",
            title: "Title",
            minResizableWidth: 50,
            filterable: {
              cell: {
                delay: 9999999999
              }
            }
          },
          {
            field: "links",
            title: "Links",
            filterable: false,
            minResizableWidth: 50,
            template: `<div class="search-engine-grid-link-result">#for (var i = 0; i < links.length; i++) {#
                    <p><a class="search-engine-link" data-role="develRouterLink" href="#: links[i].link#">#: links[i].label#</a></p>
                        #}#</div>`
          }
        ]
      })
      .data("kendoGrid");
    if (this.value) {
      this.filters = this.value;
    }
  },
  watch: {
    filters: {
      handler: function(newVal) {
        this.$emit("input", newVal);
        this.updateGridFilters();
      },
      deep: true
    },
    value(newVal) {
      this.filters = newVal;
    }
  }
};
