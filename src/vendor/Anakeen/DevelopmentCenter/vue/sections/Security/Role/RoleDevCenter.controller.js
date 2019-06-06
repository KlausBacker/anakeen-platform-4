import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import ElementView from "../../SmartElements/ElementView/ElementView.vue";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "element-view": ElementView
  },
  props: ["role"],
  data() {
    return {
      selectedRole: this.role,
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
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    } else {
      next(vueInstance => {
        // Trigger resize to resize the splitter
        vueInstance.$(window).trigger("resize");
      });
    }
  },
  mounted() {
    if (this.selectedRole) {
      this.$refs.rolesSplitter.disableEmptyContent();
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
    getRoute() {
      if (this.selectRole) {
        return Promise.resolve([
          {
            url: this.selectedRole,
            name: this.selectedRole,
            label: this.selectedRole
          }
        ]);
      }
      return Promise.resolve([]);
    },
    selectRole(e) {
      let profileId;
      switch (e.data.type) {
        case "consultRole":
          e.preventDefault();
          profileId = e.data.row.name || e.data.row.id.toString();
          this.$refs.rolesSplitter.disableEmptyContent();
          this.selectedRole = profileId;
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(e.data.row.id, "id");
          });
          break;
      }
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
