import Vue from "vue";
import { AnkSEGrid } from "@anakeen/ank-components";
import "@progress/kendo-ui";
import "@progress/kendo-ui/js/kendo.splitter";
import { AnkSmartElement } from "@anakeen/ank-components";

Vue.use(AnkSEGrid);
Vue.use(AnkSmartElement);

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-smart-element": AnkSmartElement
  },
  props: ["ssName"],
  beforeRouteEnter(to, from, next) {
    if (to.name === "Ui::masks::element") {
      next(function(vueInstance) {
        vueInstance.getSelected(to.params.seIdentifier);
      });
    } else {
      next();
    }
  },
  mounted() {
    const onContentResize = (part, $split) => {
      return () => {
        window.setTimeout(() => {
          this.$(window).trigger("resize");
        }, 100);
        window.localStorage.setItem(
          "ui.masks." + part,
          this.$($split)
            .data("kendoSplitter")
            .size(".k-pane:first")
        );
      };
    };
    this.$(this.$refs.masksSplitter).kendoSplitter({
      orientation: "horizontal",
      panes: [
        {
          scrollable: false,
          collapsible: false,
          resizable: true,
          size: window.localStorage.getItem("ui.masks.content") || "50%"
        },
        {
          scrollable: false,
          collapsible: false,
          resizable: true,
          size: "50%"
        }
      ],
      resize: onContentResize("content", this.$refs.masksSplitter)
    });
  },
  methods: {
    getSelected(e) {
      if (e !== "") {
        if (this.$refs.masksGrid.kendoGrid) {
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
      switch (event.data.type) {
        case "consult": {
          this.$router.push({
            name: "Ui::masks::element",
            params: {
              seIdentifier: event.data.row.name
            }
          });
          this.getSelected(event.data.row.name);
          break;
        }
      }
    }
  }
};
