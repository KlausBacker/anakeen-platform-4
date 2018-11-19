import Vue from "vue";
import { Splitter, LayoutInstaller } from "@progress/kendo-layout-vue-wrapper";
import { AnkSEGrid } from "@anakeen/ank-components";

Vue.use(LayoutInstaller);
Vue.use(AnkSEGrid);
export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "kendo-splitter": Splitter
  },
  data() {
    return {
      panes: [
        { scrollable: false, collapsible: true },
        { scrollable: false, collapsible: true }
      ]
    };
  },
  beforeRouteEnter(to, from, next) {
    if (to.name === "Security::Profile::Access::Element") {
      next(vueInstance => {
        vueInstance.openView();
      });
    } else {
      next();
    }
  },
  methods: {
    cellRender(event) {
      if (event.data && event.data.columnConfig) {
        switch (event.data.columnConfig.field) {
          case "family":
            event.data.cellRender.text(event.data.cellData.name);
            break;
          case "fromid":
            event.data.cellRender.text(event.data.cellData.name);
            break;
          case "dpdoc_famid":
            event.data.cellRender.text(event.data.cellData.name);
            break;
        }
      }
    },
    actionClick(event) {
      switch (event.data.type) {
        case "view": {
          this.$router.push({
            name: "Security::Profile::Access::Element",
            params: {
              seIdentifier: event.data.row.name || event.data.row.initid
            }
          });
          this.openView();
          break;
        }
      }
    },
    openView() {
      const splitter = this.$refs.splitter.kendoWidget();
      splitter.expand(".k-pane:last");
    }
  }
};
