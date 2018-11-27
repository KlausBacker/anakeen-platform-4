import Vue from "vue";
import { AnkSEGrid } from "@anakeen/ank-components";
import Splitter from "devComponents/Splitter/Splitter.vue";

Vue.use(AnkSEGrid);
export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter
  },
  data() {
    return {
      collection: "",
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
  beforeRouteEnter(to, from, next) {
    if (to.name === "Security::Roles::element") {
      let filter = to.query.role;
      next(function(vueInstance) {
        if (filter && filter !== "") {
          if (vueInstance.$refs.roleContent.kendoGrid) {
            vueInstance.$refs.roleContent.kendoGrid.dataSource.filter({
              field: "role_login",
              operator: "contains",
              value: filter
            });
          } else {
            vueInstance.$refs.roleContent.$on("grid-ready", () => {
              vueInstance.$refs.roleContent.kendoGrid.dataSource.filter({
                field: "role_login",
                operator: "contains",
                value: filter
              });
            });
          }
        }
        vueInstance.$refs.rolesSplitter.disableEmptyContent();
      });
    } else {
      next();
    }
  },
  devCenterRefreshData() {
    if (this.$refs.roleContent && this.$refs.roleContent.dataSource) {
      this.$refs.roleContent.dataSource.read();
    }
  },
  methods: {
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
      e.preventDefault();
      this.$router.push({
        name: "Security::Roles::element",
        params: {
          seIdentifier: e.data.row.name ? e.data.row.name : e.data.row.id
        }
      });
      this.getSelected(e.data.row.id);
      this.$refs.rolesSplitter.disableEmptyContent();
    },
    onGridError(event) {
      this.$store.dispatch("displayError", {
        title: "Error",
        textContent: event.data.message
      });
    },
    getSelected(e) {
      if (e !== "") {
        if (this.$refs.roleContent.kendoGrid) {
          this.$("tr[role=row]", this.$el).removeClass(
            "control-view-is-opened"
          );
          this.$(
            "tr[data-uid=" +
              this.$refs.roleContent.kendoGrid.dataSource
                .view()
                .find(d => d.rowData.id === e).uid +
              "]",
            this.$el
          ).addClass("control-view-is-opened");
        }
      }
    }
  }
};
