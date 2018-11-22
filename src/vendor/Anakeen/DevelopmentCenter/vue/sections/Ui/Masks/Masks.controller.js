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
        if (vueInstance.$refs.masksGrid.kendoGrid) {
          vueInstance.$refs.masksGrid.kendoGrid.dataSource.filter({
            field: "name",
            operator: "eq",
            value: to.params.seIdentifier
          });
          vueInstance.getSelected(to.params.seIdentifier);
          vueInstance.$refs.masksSplitter.disableEmptyContent();
        } else {
          vueInstance.$refs.masksGrid.$on("grid-ready", () => {
            vueInstance.$refs.masksGrid.kendoGrid.dataSource.filter({
              field: "name",
              operator: "eq",
              value: to.params.seIdentifier
            });
          });
          vueInstance.getSelected(to.params.seIdentifier);
          vueInstance.$refs.masksSplitter.disableEmptyContent();
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
  methods: {
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
