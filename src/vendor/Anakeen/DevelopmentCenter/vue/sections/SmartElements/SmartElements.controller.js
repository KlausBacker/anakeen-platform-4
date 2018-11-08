import Vue from "vue";
import { AnkSEGrid } from "@anakeen/ank-components";
Vue.use(AnkSEGrid);
export default {
  components: {
    "ank-se-grid": AnkSEGrid
  },
  computed: {
    urlConfig() {
      return `/api/v2/devel/security/elements/config/?vendor=${
        this.$store.getters.vendorCategory
      }`;
    }
  },
  methods: {
    cellRender(event) {
      if (event.data && event.data.columnConfig) {
        switch (event.data.columnConfig.field) {
          case "fromid":
            event.data.cellRender.text(event.data.cellData.name);
            break;
        }
      }
    },
    actionClick(event) {
      switch (event.data.type) {
        case "viewJSON":
          window.open(`/api/v2/documents/${event.data.row.id}.json`);
          break;
        case "viewXML":
          window.open(`/api/v2/documents/${event.data.row.id}.xml`);
          break;
      }
    }
  }
};
