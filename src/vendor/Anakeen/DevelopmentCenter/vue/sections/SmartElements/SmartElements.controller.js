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
      if (event.data) {
        if (event.data.columnConfig) {
          switch (event.data.columnConfig.field) {
            case "fromid":
              event.data.cellRender.text(event.data.cellData.name);
              break;
          }
        }
        if (event.data.rowData.doctype && event.data.rowData.doctype === "C") {
          event.data.cellRender.addClass("structure-type-cell");
        }
      }
    },
    actionClick(event) {
      switch (event.data.type) {
        case "viewJSON":
          switch (event.data.row.doctype) {
            case "C":
              window.open(
                `/api/v2/families/${event.data.row.id}/views/structure`
              );
              break;
            default:
              window.open(`/api/v2/documents/${event.data.row.id}.json`);
              break;
          }
          break;
        case "viewXML":
          switch (event.data.row.doctype) {
            case "C":
              window.open(
                `/api/v2/devel/config/smart/structures/${event.data.row.id}.xml`
              );
              break;
            case "W":
              window.open(
                `/api/v2/devel/config/smart/workflows/${event.data.row.id}.xml`
              );
              break;
            default:
              window.open(`/api/v2/documents/${event.data.row.id}.xml`);
          }
          break;
        case "viewProps":
          window.open(`/api/v2/documents/${event.data.row.id}.xml`);
          break;
        case "security":
          // console.log(event.data);
          break;
      }
    }
  }
};
