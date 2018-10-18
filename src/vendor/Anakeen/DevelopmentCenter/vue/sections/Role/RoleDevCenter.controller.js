import Vue from "vue";
import { AnkSEGrid } from "@anakeen/ank-components";
import VModal from "vue-js-modal";
import { AnkSmartElement } from "@anakeen/ank-components";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(VModal);
Vue.use(AnkSEGrid);
Vue.use(AnkSmartElement);
Vue.use(ButtonsInstaller);
export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-smart-element": AnkSmartElement
  },
  data() {
    return {
      collection: ""
    };
  },
  methods: {
    setCellRender(e) {
      if (e.data.columnConfig.field === "title") {
        e.data.cellRender
          .find(".grid-cell-value")
          .prepend("<img src='" + e.data.rowData.icon + "'/>  ");
      }
    },
    setGridOption() {
      const options = this.$refs.roleContent.kendoGrid.getOptions();
      options.filterable = { mode: "row" };
      options.columns.forEach(col => {
        col.filterable = {
          cell: {
            showOperators: false,
            template: e => {
              e.element.addClass("k-textbox filter-input");
            }
          },
          operators: {
            string: {
              contains: "Contains"
            }
          }
        };
      });
      this.$refs.roleContent.kendoGrid.setOptions(options);
    },
    selectRole(e) {
      this.$modal.show("roleModal");
      this.collection = e.data.row.id;
    },
    openedModal() {
      if (this.$refs.roleConsult.isLoaded()) {
        this.openConsult(this.collection);
      } else {
        this.$refs.roleConsult.$once("documentLoaded", () => {
          this.openConsult(this.collection);
        });
      }
    },
    openConsult(e) {
      this.$refs.roleConsult.fetchSmartElement({
        initid: e,
        viewId: "!defaultConsultation"
      });
    }
  }
};
