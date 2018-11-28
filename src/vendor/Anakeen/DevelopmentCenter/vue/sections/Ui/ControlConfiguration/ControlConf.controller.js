import Vue from "vue";
import { AnkSEGrid } from "@anakeen/ank-components";
import Splitter from "../../../components/Splitter/Splitter.vue";

Vue.use(Splitter);
Vue.use(AnkSEGrid);

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter
  },
  props: ["ssName"],
  beforeRouteEnter(to, from, next) {
    if (to.name === "Ui::control::element") {
      next(function(vueInstance) {
        if (to.query.filter) {
          if (vueInstance.$refs.controlConfGrid.kendoGrid) {
            vueInstance.$refs.controlConfGrid.kendoGrid.dataSource.filter({
              field: "name",
              operator: "eq",
              value: to.query.filter
            });
            vueInstance.$refs.controlSplitter.disableEmptyContent();
            vueInstance.getSelected(to.params.seIdentifier);
          } else {
            vueInstance.$refs.controlConfGrid.$on("grid-ready", () => {
              vueInstance.$refs.controlConfGrid.kendoGrid.dataSource.filter({
                field: "name",
                operator: "eq",
                value: to.query.filter
              });
            });
            vueInstance.$refs.controlSplitter.disableEmptyContent();
            vueInstance.getSelected(to.params.seIdentifier);
          }
        }
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
  data() {
    return {
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
  devCenterRefreshData() {
    if (this.$refs.controlConfGrid && this.$refs.controlConfGrid.dataSource) {
      this.$refs.controlConfGrid.dataSource.read();
    }
  },
  methods: {
    getFiltered() {
      this.$refs.controlConfGrid.kendoGrid.dataSource.bind("change", e => {
        if (e.sender._filter === undefined) {
          let query = Object.assign({}, this.$route.query);
          delete query.filter;
          this.$router.replace({ query });
        }
      });
    },
    getSelected(e) {
      this.$nextTick(() => {
        if (e !== "") {
          if (this.$refs.controlConfGrid.kendoGrid) {
            this.$("[role=row]", this.$el).removeClass(
              " control-view-is-opened"
            );
            this.$(
              "[data-uid=" +
                this.$refs.controlConfGrid.kendoGrid.dataSource
                  .view()
                  .find(d => d.rowData.name === e).uid +
                "]",
              this.$el
            ).addClass(" control-view-is-opened");
          }
        }
      });
    },
    actionClick(event) {
      event.preventDefault();
      this.$refs.controlSplitter.disableEmptyContent();
      switch (event.data.type) {
        case "consult":
          this.$router.push({
            name: "Ui::control::element",
            params: {
              seIdentifier: event.data.row.name
            }
          });
          this.getSelected(event.data.row.name);
          break;
        case "permissions":
          this.$router.push({
            name: "Ui::control::permissions",
            params: {
              seIdentifier: event.data.row.name
            }
          });
          this.getSelected(event.data.row.name);
          break;
        default:
          break;
      }
    }
  }
};
