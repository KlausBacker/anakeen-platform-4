import Vue from "vue";
import Splitter from "../../../components/Splitter/Splitter.vue";
import { AnkSEGrid } from "@anakeen/ank-components";

Vue.use(Splitter);
Vue.use(AnkSEGrid);
export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter
  },
  data() {
    return {
      splitterProfileEmpty: true,
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: "50%"
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
      this.splitterProfileEmpty = false;
      switch (event.data.type) {
        case "view": {
          this.$router.push({
            name: "Security::Profile::Access::Element",
            params: {
              seIdentifier: event.data.row.name || event.data.row.initid
            }
          });
          break;
        }
      }
    }
  }
};
