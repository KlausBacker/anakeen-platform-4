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
          if (vueInstance.$refs.masksGrid.kendoGrid) {
            vueInstance.$refs.masksConsult.fetchSmartElement({
              initid: filter,
              viewId: "!defaultConsultation"
            });
          } else {
            vueInstance.$refs.masksGrid.$on("grid-ready", () => {
              vueInstance.$refs.masksConsult.$once("documentLoaded", () => {
                vueInstance.$refs.masksConsult.fetchSmartElement({
                  initid: filter,
                  viewId: "!defaultConsultation"
                });
                vueInstance
                  .$(
                    "[data-uid=" +
                      vueInstance.$refs.masksGrid.kendoGrid.dataSource
                        .view()
                        .find(d => d.rowData.name === filter).uid +
                      "]",
                    vueInstance.$el
                  )
                  .addClass("masks-view-is-opened");
              });
            });
          }
        }
      });
    } else {
      next(function(vueInstance) {
        vueInstance.$refs.masksGrid.$on("grid-ready", () => {
          vueInstance.$refs.masksGrid.kendoGrid.dataSource.filter({});
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
          collapsible: false,
          resizable: true,
          size: window.localStorage.getItem("ui.masks.content") || "50%"
        },
        { collapsible: false, resizable: true, size: "50%" }
      ],
      resize: onContentResize("content", this.$refs.masksSplitter)
    });
  },
  methods: {
    actionClick(event) {
      event.preventDefault();
      switch (event.data.type) {
        case "consult": {
          this.$refs.masksConsult.fetchSmartElement({
            initid: event.data.row.id,
            viewId: "!defaultConsultation"
          });
          this.$("[role=row]", this.$el).removeClass("masks-view-is-opened");
          this.$(event.target)
            .closest("tr")
            .addClass("masks-view-is-opened");
          break;
        }
      }
    }
  }
};
