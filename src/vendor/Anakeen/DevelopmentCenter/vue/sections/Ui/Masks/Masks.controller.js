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
          vueInstance.splitterMasksEmpty = false;
          vueInstance.getSelected(to.params.seIdentifier);
        } else {
          vueInstance.$refs.masksGrid.$on("grid-ready", () => {
            vueInstance.$refs.masksGrid.kendoGrid.dataSource.filter({
              field: "name",
              operator: "eq",
              value: to.params.seIdentifier
            });
          });
          vueInstance.splitterMasksEmpty = false;
          vueInstance.getSelected(to.params.seIdentifier);
        }
      });
    } else {
      next();
    }
  },
  data() {
    return {
      splitterMasksEmpty: true,
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size:
            window.localStorage.getItem("ui.masks.content." + this.ssName) ||
            "50%"
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
  mounted() {
    this.$refs.masksSplitter.$refs.ankSplitter
      .kendoWidget()
      .bind(
        "resize",
        this.onContentResize(
          this.$refs.masksSplitter.$refs.ankSplitter.kendoWidget()
        )
      );
  },
  methods: {
    onContentResize(kendoSplitter) {
      return () => {
        window.setTimeout(() => {
          this.$(window).trigger("resize");
        }, 100);
        window.localStorage.setItem(
          "ui.masks.content." + this.ssName,
          kendoSplitter.size(".k-pane:first")
        );
      };
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
      this.splitterMasksEmpty = false;
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
