import Vue from "vue";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.filtercell.js";
import "@progress/kendo-ui";
import "@progress/kendo-ui/js/kendo.splitter";
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
  props: ["wflName"],
  data() {
    return {
      transitionsDataSource: "",
      columnsTabMultiple: [
        "mailtemplates",
        "volatileTimers",
        "unAttachTimers",
        "persistentTimers"
      ]
    };
  },
  mounted() {
    $(window).resize(() => {
      if (this.$refs.transitionsGridContent) {
        this.$refs.transitionsGridContent.kendoWidget().resize();
      }
    });
    const onContentResize = (part, $split) => {
      return () => {
        window.setTimeout(() => {
          this.$(window).trigger("resize");
        }, 100);
        window.localStorage.setItem(
          "wfl.transition." + part,
          this.$($split)
            .data("kendoSplitter")
            .size(".k-pane:first")
        );
      };
    };
    this.$(this.$refs.transitionsSplitter).kendoSplitter({
      orientation: "horizontal",
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: window.localStorage.getItem("wfl.transition.content") || "50%"
        },
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
        }
      ],
      resize: onContentResize("content", this.$refs.transitionsSplitter)
    });
  },
  methods: {
    getTransitions(options) {
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
    parseTransitionsData(response) {
      if (response && response.data && response.data.data) {
        return response.data.data.transitions;
      }
      return [];
    },
    refreshTransitions() {
      this.$refs.transitionsGridContent.kendoWidget().dataSource.filter({});
      this.$refs.transitionsGridContent.kendoWidget().dataSource.read();
    },
    displayMultiple(colId) {
      return dataItem => {
        if (dataItem[colId] === null || dataItem[colId] === undefined) {
          return "";
        }
        if (dataItem[colId] instanceof Object) {
          if (this.columnsTabMultiple.includes(colId)) {
            let str = "";
            return this.recursiveData(dataItem[colId], str, colId);
          }
        }
        return dataItem[colId];
      };
    },
    recursiveData(items, str, colId) {
      if (items instanceof Object) {
        Object.keys(items.toJSON()).forEach(item => {
          if (items[item] instanceof Object) {
            this.recursiveData(items[item], str, colId);
          } else {
            switch (colId) {
              case "mailtemplates":
                str += `<a data-role="develRouterLink" href="/devel/wfl/${
                  this.wflName
                }/transitions/mail/${
                  items[item]
                }" style="text-decoration: underline; color: #157EFB">${
                  items[item]
                }</a>`;
                break;
              case "volatileTimers":
                str += `<a data-role="develRouterLink" href="/devel/wfl/${
                  this.wflName
                }/transitions/timers/volatile/${
                  items[item]
                }" style="text-decoration: underline; color: #157EFB">${
                  items[item]
                }</a>`;
                break;
              case "persistentTimers":
                str += `<a data-role="develRouterLink" href="/devel/wfl/${
                  this.wflName
                }/transitions/timers/persistent/${
                  items[item]
                }" style="text-decoration: underline; color: #157EFB">${
                  items[item]
                }</a>`;
                break;
              case "unattachTimers":
                str += `<a data-role="develRouterLink" href="/devel/wfl/${
                  this.wflName
                }/transitions/timers/unattach/${
                  items[item]
                }" style="text-decoration: underline; color: #157EFB">${
                  items[item]
                }</a>`;
                break;
              default:
                break;
            }
          }
        });
      }
      return str;
    }
  }
};
