import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import ElementView from "../../SmartElements/ElementView/ElementView.vue";
import ProfileView from "devComponents/profile/profile.vue";
import Splitter from "@anakeen/internal-components/lib/Splitter.js";

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "element-view": ElementView,
    "profile-view": ProfileView
  },
  props: ["ssName", "controlConfig"],
  watch: {
    controlConfig(newValue) {
      this.$refs.controlSplitter.disableEmptyContent();
      this.selectedControl = newValue;
    }
  },
  mounted() {
    if (this.selectedControl) {
      this.$refs.controlSplitter.disableEmptyContent();
    }
  },
  data() {
    return {
      selectedControl: this.controlConfig,
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
    if (this.$refs.controlConfGrid && this.$refs.controlConfGrid.dataSource) {
      this.$refs.controlConfGrid.dataSource.read();
    }
  },
  methods: {
    getFiltered() {
      // this.$refs.controlConfGrid.kendoGrid.dataSource.bind("change", e => {
      //   if (e.sender._filter === undefined) {
      //     let query = Object.assign({}, this.$route.query);
      //     delete query.filter;
      //     this.$router.replace({ query });
      //   }
      // });
    },
    getSelected(e) {
      this.$nextTick(() => {
        if (e !== "") {
          if (this.$refs.controlConfGrid.kendoGrid) {
            this.$("[role=row]", this.$el).removeClass(
              " control-view-is-opened"
            );
            this.$(
              "[data-uid=" +
                this.$refs.controlConfGrid.kendoGrid.dataSource
                  .view()
                  .find(d => d.rowData.name === e).uid +
                "]",
              this.$el
            ).addClass(" control-view-is-opened");
          }
        }
      });
    },
    getRoute() {
      if (this.selectedControl) {
        return Promise.resolve([this.selectedControl]);
      }
      return Promise.resolve([]);
    },
    actionClick(event) {
      const controlName = event.data.row.name;
      switch (event.data.type) {
        case "consult":
          event.preventDefault();
          this.$refs.controlSplitter.disableEmptyContent();
          this.selectedControl = {
            url: `${this.ssName}/control/element/${controlName}`,
            component: "element-view",
            props: {
              initid: controlName
            },
            name: controlName,
            label: controlName
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(event.data.row.name);
          });
          break;
        case "permissions":
          event.preventDefault();
          this.$refs.controlSplitter.disableEmptyContent();
          this.selectedControl = {
            url: `${this.ssName}/control/element/${controlName}`,
            component: "profile-view",
            props: {
              profileId: controlName.toString(),
              detachable: true,
              onlyExtendedAcls: true
            },
            name: controlName,
            label: controlName
          };
          this.getRoute().then(route => {
            this.$emit("navigate", route);
            this.getSelected(event.data.row.name);
          });
          break;
        default:
          break;
      }
    }
  }
};
