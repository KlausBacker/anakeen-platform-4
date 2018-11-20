import Vue from "vue";
import Splitter from "../../../components/Splitter/Splitter.vue";
import { AnkSEGrid } from "@anakeen/ank-components";

Vue.use(Splitter);
Vue.use(AnkSEGrid);
export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter
  },
  data() {
    return {
      panes: [
        {
          scrollable: false,
          collapsible: true,
          resizable: true,
          size: window.localStorage.getItem("profile.content") || "50%"
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
    if (to.name === "Security::Profile::Access::Element") {
      next(vueInstance => {
        vueInstance.openView();
      });
    } else {
      next();
    }
  },
  mounted() {
    this.$refs.profileSplitter.$refs.ankSplitter
      .kendoWidget()
      .bind(
        "resize",
        this.onContentResize(
          this.$refs.profileSplitter.$refs.ankSplitter.kendoWidget()
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
          "profile.content",
          kendoSplitter.size(".k-pane:first")
        );
      };
    },
    cellRender(event) {
      if (event.data && event.data.columnConfig) {
        switch (event.data.columnConfig.field) {
          case "family":
            event.data.cellRender.text(event.data.cellData.name);
            break;
          case "fromid":
            event.data.cellRender.text(event.data.cellData.name);
            break;
          case "dpdoc_famid":
            event.data.cellRender.text(event.data.cellData.name);
            break;
        }
      }
    },
    actionClick(event) {
      switch (event.data.type) {
        case "view": {
          this.$router.push({
            name: "Security::Profile::Access::Element",
            params: {
              seIdentifier: event.data.row.name || event.data.row.initid
            }
          });
          this.openView();
          break;
        }
      }
    },
    openView() {
      const splitter = this.$refs.splitter.kendoWidget();
      splitter.expand(".k-pane:last");
    }
  }
};
