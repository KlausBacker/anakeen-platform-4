import Vue from "vue";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import Splitter from "../../../components/Splitter/Splitter.vue";
import { Grid, GridInstaller } from "@progress/kendo-grid-vue-wrapper";
import { DataSourceInstaller } from "@progress/kendo-datasource-vue-wrapper";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(GridInstaller);
Vue.use(DataSourceInstaller);
Vue.use(ButtonsInstaller);
Vue.use(Splitter);

export default {
  components: {
    Grid,
    "ank-splitter": Splitter
  },
  props: ["wflName"],
  data() {
    return {
      splitterStepsEmpty: true,
      stepsDataSource: "",
      ssName: "",
      routeTab: ["Wfl::steps::pdoc"],
      columnsTabMultiple: ["mailtemplates"],
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size:
            window.localStorage.getItem("wfl.steps.content." + this.wflName) ||
            "50%"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        }
      ]
    };
  },
  beforeRouteEnter(to, from, next) {
    next(function(vueInstance) {
      if (vueInstance.routeTab.includes(to.name)) {
        vueInstance.splitterStepsEmpty = false;
      }
    });
  },
  beforeRouteUpdate(to, from, next) {
    this.splitterStepsEmpty = false;
    next();
  },
  mounted() {
    this.$refs.stepsSplitter.$refs.ankSplitter
      .kendoWidget()
      .bind(
        "resize",
        this.onContentResize(
          this.$refs.stepsSplitter.$refs.ankSplitter.kendoWidget()
        )
      );
  },
  methods: {
    onContentResize(kendoSplitter) {
      return () => {
        window.setTimeout(() => {
          this.$(window).trigger("resize");
        }, 100);
        window.localStorage.setItem(
          "wfl.steps.content." + this.wflName,
          kendoSplitter.size(".k-pane:first")
        );
      };
    },
    getSteps(options) {
      this.$http
        .get(`/api/v2/devel/smart/workflows/${this.wflName}`, {
          params: options.data,
          paramsSerializer: kendo.jQuery.param
        })
        .then(response => {
          options.success(response);
        })
        .catch(response => {
          options.error(response);
        });
      return [];
    },
    parseStepsData(response) {
      if (response && response.data && response.data.data) {
        this.ssName = response.data.data.properties.structure;
        return response.data.data.steps;
      }
      return [];
    },
    refreshSteps() {
      this.$refs.stepsGridContent.kendoWidget().dataSource.filter({});
      this.$refs.stepsGridContent.kendoWidget().dataSource.read();
    },
    autoFilterCol(e) {
      e.element.addClass("k-textbox filter-input");
    },
    displayColor(colId) {
      return dataItem => {
        return `<div class='chip-color' style='background-color:${
          dataItem[colId]
        }'></div><span>&nbsp${dataItem[colId]}</span>`;
      };
    },
    displayMultiple(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        if (dataItem[colId] instanceof Object) {
          if (this.columnsTabMultiple.includes(colId)) {
            if (dataItem[colId].length > 1) {
              let str = "";
              return this.recursiveData(dataItem[colId], str);
            } else {
              return dataItem[colId][0] ? dataItem[colId][0] : "";
            }
          }
        }
        return dataItem[colId];
      };
    },
    recursiveData(items, str) {
      if (items instanceof Object) {
        Object.keys(items.toJSON()).forEach(item => {
          if (items[item] instanceof Object) {
            this.recursiveData(items[item], str);
          } else {
            str += "<li>" + items[item] + "</li>";
          }
        });
      }
      return str;
    },
    displayLink(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem === undefined) {
          return "";
        } else {
          switch (colId) {
            case "viewcontrol":
              return `<a data-role="develRouterLink" href="/devel/ui/${
                this.ssName
              }/views" style="text-decoration: underline; color: #157EFB">${
                dataItem[colId]
              }</a>`;

            case "mask":
              return `<a data-role="develRouterLink" href="/devel/ui/${
                this.ssName
              }/views" style="text-decoration: underline; color: #157EFB">${
                dataItem[colId]
              }</a>`;
            case "profil":
              return `<a data-role="develRouterLink" href="/devel/wfl/${
                this.wflName
              }/steps/pdoc/${
                dataItem[colId]
              }" style="text-decoration: underline; color: #157EFB">${
                dataItem[colId]
              }</a>`;
            default:
              return dataItem[colId];
          }
        }
      };
    }
  }
};
