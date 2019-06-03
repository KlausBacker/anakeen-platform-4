import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import ElementView from "../../SmartElements/ElementView/ElementView.vue";
import ProfileView from "devComponents/profile/profile.vue";
import Splitter from "../../../components/Splitter/Splitter.vue";

const addToArray = (anArray = [], item, filterCb = () => false) => {
  if (anArray) {
    if (typeof filterCb === "function") {
      const filterResult = anArray.filter(filterCb);
      if (!filterResult || filterResult.length) {
        anArray.push(item);
      }
    } else {
      anArray.push(item);
    }
  }
  return anArray;
};

export default {
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-splitter": Splitter,
    "element-view": ElementView,
    "profile-view": ProfileView
  },
  props: ["ssName"],
  data() {
    return {
      openedItems: [],
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
      this.$refs.controlConfGrid.kendoGrid.dataSource.bind("change", e => {
        if (e.sender._filter === undefined) {
          let query = Object.assign({}, this.$route.query);
          delete query.filter;
          this.$router.replace({ query });
        }
      });
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
    actionClick(event) {
      event.preventDefault();
      this.$refs.controlSplitter.disableEmptyContent();
      switch (event.data.type) {
        case "consult":
          addToArray(
            this.openedItems,
            {
              name: event.data.row.name
            },
            item => item.name === event.data.row.name
          );
          this.getSelected(event.data.row.name);
          console.log(this.openedItems);
          break;
        case "permissions":
          this.getSelected(event.data.row.name);
          break;
        default:
          break;
      }
    }
  }
};
