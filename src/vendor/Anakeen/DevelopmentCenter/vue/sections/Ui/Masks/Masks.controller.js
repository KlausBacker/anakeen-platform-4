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
    if (to.name === "Ui::masks::element") {
      next(function(vueInstance) {
        if (to.query.filter) {
          if (vueInstance.$refs.masksGrid.kendoGrid) {
            vueInstance.$refs.masksGrid.kendoGrid.dataSource.filter({
              field: "name",
              operator: "eq",
              value: to.query.filter
            });
            vueInstance.$refs.masksSplitter.disableEmptyContent();
            vueInstance.getSelected(to.params.seIdentifier);
          } else {
            vueInstance.$refs.masksGrid.$on("grid-ready", () => {
              vueInstance.$refs.masksGrid.kendoGrid.dataSource.filter({
                field: "name",
                operator: "eq",
                value: to.query.filter
              });
            });
            vueInstance.$refs.masksSplitter.disableEmptyContent();
            vueInstance.getSelected(to.params.seIdentifier);
          }
        }
      });
    } else {
      next();
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
    if (this.$refs.masksGrid && this.$refs.masksGrid.dataSource) {
      this.$refs.masksGrid.dataSource.read();
    }
  },
  methods: {
    getFiltered() {
      this.$refs.masksGrid.kendoGrid.dataSource.bind("change", e => {
        if (e.sender._filter === undefined) {
          let query = Object.assign({}, this.$route.query);
          delete query.filter;
          this.$router.replace({ query });
        }
      });
    },
    getSelected(e, col) {
      if (e !== "") {
        if (this.$refs.masksGrid.kendoGrid) {
          if (col === "id") {
            this.$("[role=row]", this.$el).removeClass(
              "control-view-is-opened"
            );
            this.$(
              "[data-uid=" +
                this.$refs.masksGrid.kendoGrid.dataSource
                  .view()
                  .find(d => d.rowData.id === e).uid +
                "]",
              this.$el
            ).addClass("control-view-is-opened");
          }
        } else if (col === "name") {
          this.$("[role=row]", this.$el).removeClass("control-view-is-opened");
          this.$(
            "[data-uid=" +
              this.$refs.masksGrid.kendoGrid.dataSource
                .view()
                .find(d => d.rowData.name === e).uid +
              "]",
            this.$el
          ).addClass("control-view-is-opened");
        }
      }
    },
    actionClick(event) {
      event.preventDefault();
      this.$refs.masksSplitter.disableEmptyContent();
      switch (event.data.type) {
        case "consult": {
          this.$router.push({
            name: "Ui::masks::element",
            params: {
              seIdentifier: event.data.row.name
                ? event.data.row.name
                : event.data.row.id
            }
          });
          this.getSelected(event.data.row.id, "id");
          break;
        }
      }
    }
  }
};
