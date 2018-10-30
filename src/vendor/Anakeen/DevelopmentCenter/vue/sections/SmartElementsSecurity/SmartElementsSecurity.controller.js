import Vue from "vue";
import { AnkSEGrid } from "@anakeen/ank-components";
Vue.use(AnkSEGrid);
export default {
  components: {
    "ank-se-grid": AnkSEGrid
  },
  methods: {
    cellRender(event) {
      if (event.data && event.data.columnConfig) {
        switch (event.data.columnConfig.field) {
          case "family":
            event.data.cellRender.text(event.data.cellData.name);
            break;
          case "dpdoc_famid":
            event.data.cellRender.text(event.data.cellData.name);
            break;
        }
      }
    }
  }
};
