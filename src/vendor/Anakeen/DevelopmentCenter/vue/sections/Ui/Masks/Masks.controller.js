import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import ElementView from "../../SmartElements/ElementView/ElementView.vue";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "element-view": ElementView
  },
  props: ["ssName", "mask"],
  watch: {
    mask(newValue) {
      this.$refs.masksSplitter.disableEmptyContent();
      this.selectedMask = newValue;
    }
  },
  mounted() {
    if (this.selectedMask) {
      this.$refs.masksSplitter.disableEmptyContent();
    }
  },
  data() {
    return {
      selectedMask: this.mask,
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
    getRoute() {
      if (this.selectedMask) {
        return Promise.resolve([
          {
            url: this.selectedMask,
            name: this.selectedMask,
            label: this.selectedMask
          }
        ]);
      }
      return Promise.resolve([]);
    },
    actionClick(event) {
      event.preventDefault();
      switch (event.data.type) {
        case "consult": {
          this.$refs.masksSplitter.disableEmptyContent();
          this.selectedMask = event.data.row.name || event.data.row.id;
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(event.data.row.id, "id");
          });
          break;
        }
      }
    }
  }
};
