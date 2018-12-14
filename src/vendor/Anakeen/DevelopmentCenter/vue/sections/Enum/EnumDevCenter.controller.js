import Vue from "vue";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
Vue.use(ButtonsInstaller);

const parseFilters = filters => {
  const result = {};
  if (filters) {
    filters.split("&").forEach(filter => {
      const entry = filter.split("=");
      if (entry && entry.length) {
        const key = entry[0];
        const value = entry[1];
        result[key] = value;
      }
    });
    return result;
  } else {
    return null;
  }
};

export default {
  components: {
    Grid
  },
  data() {
    return {
      enumDataSource: ""
    };
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
  },
  methods: {
    bindFilters() {
      this.$refs.enumGridContent.kendoWidget().bind("filter", event => {
        const filter = event.filter ? event.filter.filters[0] || null : null;
        if (filter) {
          this.$router.addQueryParams({
            filters: this.$.param(
              Object.assign(
                {},
                this.$route.query.filters
                  ? parseFilters(this.$route.query.filters)
                  : {},
                { [filter.field]: filter.value }
              )
            )
          });
        } else {
          const query = Object.assign({}, this.$route.query);
          if (query.filters) {
            query.filters = parseFilters(query.filters);
            delete query.filters[event.field];
            if (!Object.keys(query.filters).length) {
              delete query.filters;
            } else {
              query.filters = this.$.param(query.filters);
            }
          }
          this.$router.push({ query: query });
        }
      });
      this.$refs.enumGridContent.kendoWidget().bind("filter", e => {
        if (e.filter === null) {
          let query = Object.assign({}, this.$route.query);
          delete query.filters;
          this.$router.replace({ query });
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
    }
  }
};
