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
  mounted() {
    $(window).resize(() => {
      if (this.$refs.viewsGridContent) {
        this.$refs.viewsGridContent.kendoWidget().resize();
      }
    });
  },
  devCenterRefreshData() {
    if (this.$refs.viewsGrid) {
      this.$refs.viewsGrid.kendoWidget().read();
    }
  },
  beforeRouteEnter(to, from, next) {
    if (to.query.col && to.query.filter) {
      next(function(vueInstance) {
        if (vueInstance.$refs.viewsGridContent.kendoWidget()) {
          vueInstance.$refs.viewsGridContent.kendoWidget().dataSource.filter({
            field: to.query.col,
            operator: "eq",
            value: to.query.filter
          });
        } else {
          vueInstance.$refs.viewsGridContent.$on("grid-ready", () => {
            vueInstance.$refs.viewsGridContent.kendoWidget().dataSource.filter({
              field: to.query.col,
              operator: "eq",
              value: to.query.filter
            });
          });
        }
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    } else {
      next(vueInstance => {
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    }
  },
  methods: {
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
            return `<a data-role="develRouterLink" href="/devel/ui/${
              this.ssName
            }/masks/${dataItem[colId]}/?filter=${dataItem[colId]}">${
              dataItem[colId]
            }</a>`;
          case "cvId":
            return `<a data-role="develRouterLink" href="/devel/ui/${
              this.ssName
            }/control/element/${dataItem[colId]}/?filter=${dataItem[colId]}">${
              dataItem[colId]
            }</a>`;
          case "cvStructure":
            return `<a data-role="develRouterLink" href="/devel/smartStructures/${
              dataItem[colId]
            }/infos">${dataItem[colId]}</a>`;
          default:
            break;
        }
      };
    }
  }
};
