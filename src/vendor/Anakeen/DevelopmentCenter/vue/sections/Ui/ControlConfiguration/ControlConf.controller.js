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
        vueInstance.$refs.controlConfGrid.kendoGrid.dataSource.filter({
          field: "name",
          operator: "eq",
          value: to.params.seIdentifier
        });
        vueInstance.getSelected(to.params.seIdentifier);
      });
    } else {
      next();
    }
  },
  data() {
    return {
      splitterControlConfEmpty: true,
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size:
            window.localStorage.getItem(
              "ui.control.conf.content." + this.ssName
            ) || "50%"
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
    this.$refs.controlSplitter.$refs.ankSplitter
      .kendoWidget()
      .bind(
        "resize",
        this.onContentResize(
          this.$refs.controlSplitter.$refs.ankSplitter.kendoWidget()
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
          "ui.control.conf.content." + this.ssName,
          kendoSplitter.size(".k-pane:first")
        );
      };
    },
    getSelected(e) {
      if (e !== "") {
        if (this.$refs.controlConfGrid.kendoGrid) {
          this.$("[role=row]", this.$el).removeClass("control-view-is-opened");
          this.$(
            "[data-uid=" +
              this.$refs.controlConfGrid.kendoGrid.dataSource
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
      this.splitterControlConfEmpty = false;
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
