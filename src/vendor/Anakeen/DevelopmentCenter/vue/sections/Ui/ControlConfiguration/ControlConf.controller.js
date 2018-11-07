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
  mounted() {
    const onContentResize = (part, $split) => {
      return () => {
        window.setTimeout(() => {
          this.$(window).trigger("resize");
        }, 100);
        window.localStorage.setItem(
          "ui.control.conf." + part,
          this.$($split)
            .data("kendoSplitter")
            .size(".k-pane:first")
        );
      };
    };
    this.$(this.$refs.controlConfSplitter).kendoSplitter({
      orientation: "horizontal",
      panes: [
        {
          collapsible: false,
          resizable: true,
          size: window.localStorage.getItem("ui.control.conf.content") || "50%"
        },
        { collapsible: false, resizable: true, size: "50%" }
      ],
      resize: onContentResize("content", this.$refs.controlConfSplitter)
    });
  },
  methods: {
    actionClick(event) {
      event.preventDefault();
      switch (event.data.type) {
        case "consult": {
          this.$refs.controlConfConsult.fetchSmartElement({
            initid: event.data.row.id,
            viewId: "!defaultConsultation"
          });
          break;
        }
      }
    }
  }
};
