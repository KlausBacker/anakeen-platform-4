import { Vue } from "vue-property-decorator";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
Vue.use(ButtonsInstaller);

export default {
  components: {
    Grid
  },
  data() {
    return {
      enumDataSource: ""
    };
  },
  props: ["name", "localeKey", "label", "parentkey", "disabled"],
  watch: {
    name(newValue) {
      this.updateFilterValues({ name: newValue });
    },
    localeKey(newValue) {
      this.updateFilterValues({ localeKey: newValue });
    },
    label(newValue) {
      this.updateFilterValues({ label: newValue });
    },
    parentkey(newValue) {
      this.updateFilterValues({ parentkey: newValue });
    },
    disabled(newValue) {
      this.updateFilterValues({ disabled: newValue });
    }
  },
  devCenterRefreshData() {
    if (this.$refs.enumGrid) {
      this.$refs.enumGrid.kendoWidget().read();
    }
  },
  beforeRouteEnter(to, from, next) {
    if (to.query.filters) {
      let filter = to.query.filters.split("=");
      next(function(vueInstance) {
        if (filter && filter !== "") {
          vueInstance.$refs.enumGridContent.kendoWidget().dataSource.filter({
            field: filter[0],
            operator: "contains",
            value: filter[1]
          });
        }
      });
    } else {
      next(function(vueInstance) {
        vueInstance.$refs.enumGridContent.kendoWidget().dataSource.filter({});
      });
    }
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.enumGridContent) {
        this.$refs.enumGridContent.kendoWidget().resize();
      }
    });
    this.updateFilterValues();
  },
  methods: {
    bindFilters() {
      this.$refs.enumGridContent.kendoWidget().bind("filter", event => {
        const filter = event.filter ? event.filter.filters[0] || null : null;
        if (filter) {
          const currentFilter = event.sender.dataSource.filter();
          let nextFilter = {};
          if (currentFilter) {
            nextFilter = currentFilter.filters.reduce((acc, curr) => {
              acc[curr.field] = curr.value;
              return acc;
            }, {});
          }
          this.$emit("filter", Object.assign({}, nextFilter, { [filter.field]: filter.value }));
        } else {
          const currentFilter = event.sender.dataSource.filter();
          let nextFilter = {};
          if (currentFilter) {
            nextFilter = currentFilter.filters.reduce((acc, curr) => {
              if (curr.field !== event.field) {
                acc[curr.field] = curr.value;
              }
              return acc;
            }, {});
          }
          this.$emit("filter", Object.assign({}, nextFilter));
        }
      });
    },
    getEnum(options) {
      this.$http
        .get("/api/v2/devel/smart/enumerates/", {
          params: options.data,
          paramsSerializer: kendo.jQuery.param
        })
        .then(response => {
          options.success(response);
        })
        .catch(response => {
          options.error(response);
        });
    },
    parseEnumData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.enumerates;
      }
      return [];
    },
    parseEnumTotal(response) {
      return response.data.data.requestParameters.total;
    },
    disabledFilter(args) {
      args.element.kendoDropDownList({
        valuePrimitive: true,
        dataSource: ["true", "false"]
      });
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    updateFilterValues(mergedFilters = {}) {
      const filters = [];
      this.$options._propKeys.forEach(propKey => {
        const realKey = propKey === "localeKey" ? "key" : propKey;
        const value = mergedFilters[propKey] || this[propKey];
        if (value) {
          filters.push({
            field: realKey,
            operator: "contains",
            value
          });
        }
      });
      this.$refs.enumGridContent.kendoWidget().dataSource.filter(filters);
    }
  }
};
