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
      transitionsDataSource: "",
      columnsTabMultiple: [
        "mailtemplates",
        "volatileTimers",
        "unAttachTimers",
        "persistentTimers"
      ],
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size:
            window.localStorage.getItem(
              "wfl.transitions.content." + this.wflName
            ) || "50%"
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
  mounted() {
    this.$refs.ankSplitter.$refs.ankSplitter
      .kendoWidget()
      .bind(
        "resize",
        this.onContentResize(
          this.$refs.ankSplitter.$refs.ankSplitter.kendoWidget()
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
          "wfl.transitions.content." + this.wflName,
          kendoSplitter.size(".k-pane:first")
        );
      };
    },
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