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
    if (to.query.open) {
      let filter = to.query.open;
      next(function(vueInstance) {
        if (filter && filter !== "") {
          if (vueInstance.$refs.controlConfGrid.kendoGrid) {
            vueInstance.$refs.controlConfConsult.fetchSmartElement({
              initid: filter,
              viewId: "!defaultConsultation"
            });
          } else {
            vueInstance.$refs.controlConfGrid.$on("grid-ready", () => {
              vueInstance.$refs.controlConfConsult.$once(
                "documentLoaded",
                () => {
                  vueInstance.$refs.controlConfConsult.fetchSmartElement({
                    initid: filter,
                    viewId: "!defaultConsultation"
                  });
                  vueInstance
                    .$(
                      "[data-uid=" +
                        vueInstance.$refs.controlConfGrid.kendoGrid.dataSource
                          .view()
                          .find(d => d.rowData.name === filter).uid +
                        "]",
                      vueInstance.$el
                    )
                    .addClass("control-view-is-opened");
                }
              );
            });
          }
        }
      });
    } else {
      next(function(vueInstance) {
        vueInstance.$refs.controlConfGrid.$on("grid-ready", () => {
          vueInstance.$refs.controlConfGrid.kendoGrid.dataSource.filter({});
        });
      });
    }
  },
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
          scrollable: false,
          collapsible: false,
          resizable: true,
          size: window.localStorage.getItem("ui.control.conf.content") || "50%"
        },
        {
          scrollable: false,
          collapsible: false,
          resizable: true,
          size: "50%"
        }
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
          this.$("[role=row]", this.$el).removeClass("control-view-is-opened");
          this.$(event.target)
            .closest("tr")
            .addClass("control-view-is-opened");
          break;
        }
      }
    }
  }
};
