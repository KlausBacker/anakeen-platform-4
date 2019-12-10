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
  props: ["ssName"],
  data() {
    return {
      viewsDataSource: ""
    };
  },
  updated() {
    this.initFilters(window.location.search);
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.viewsGridContent) {
        this.$refs.viewsGridContent.kendoWidget().resize();
      }
    });
    this.initFilters(window.location.search);
  },
  devCenterRefreshData() {
    if (this.$refs.viewsGrid) {
      this.$refs.viewsGrid.kendoWidget().read();
    }
  },
  methods: {
    initFilters(searchUrl) {
      const computeFilters = () => {
        const re = /(cvId|cvStructure|viewId|viewLabel|maskId|order|viewMode|renderConfigClass|menuList|displayed)=([^&]+)/g;
        let match;
        const filters = [];
        while ((match = re.exec(searchUrl))) {
          if (match && match.length >= 3) {
            const field = match[1];
            const value = decodeURIComponent(match[2]);
            filters.push({
              field,
              operator: "contains",
              value
            });
          }
        }
        if (filters.length) {
          this.$refs.viewsGrid.kendoWidget().filter(filters);
        }
      };
      if (this.$refs.viewsGrid && this.$refs.viewsGrid.kendoWidget()) {
        computeFilters();
      } else {
        this.$refs.viewsGrid.$once("hook:mounted", () => {
          computeFilters();
        });
      }
    },
    onGridDataBound() {
      this.getRoute().then(route => {
        this.$emit("navigate", route);
      });
    },
    getFilter() {
      if (this.$refs.viewsGrid) {
        const currentFilter = this.$refs.viewsGrid.kendoWidget().filter();
        if (currentFilter) {
          const filters = currentFilter.filters;
          return filters.reduce((acc, curr) => {
            acc[curr.field] = curr.value;
            return acc;
          }, {});
        }
      }
      return {};
    },
    getRoute() {
      const filter = this.getFilter();
      const filterUrl = Object.keys(filter).length ? `?${$.param(filter)}` : "";
      return Promise.resolve([{ url: filterUrl }]);
    },
    getViews(options) {
      this.$http
        .get(`/api/v2/devel/ui/smart/structures/${this.ssName}/views/`, {
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
    parseViewsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.views;
      }
      return [];
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayLink(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        switch (colId) {
          case "maskId":
            return `<a data-role="develRouterLink" href="/devel/ui/${this.ssName}/masks/${dataItem[colId]}/?name=${dataItem[colId]}">${dataItem[colId]}</a>`;
          case "cvId":
            return `<a data-role="develRouterLink" href="/devel/ui/${this.ssName}/control/element/${dataItem[colId]}/?name=${dataItem[colId]}">${dataItem[colId]}</a>`;
          case "cvStructure":
            return `<a data-role="develRouterLink" href="/devel/smartStructures/${dataItem[colId]}/infos">${dataItem[colId]}</a>`;
          default:
            break;
        }
      };
    }
  }
};
