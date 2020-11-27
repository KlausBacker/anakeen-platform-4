import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import PropertiesView from "../SmartStructureInformations/PropertiesView/PropertiesView.vue";
import ElementView from "./ElementView/ElementView.vue";

export default {
  mixins: [AnkI18NMixin],
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-split-panes": AnkPaneSplitter,
    "element-view": ElementView,
    "element-properties": PropertiesView
  },
  props: ["ssName", "ssInfos"],
  watch: {
    ssInfos(newValue) {
      if (newValue.find(element => element.id === "workflow").value.id !== 0) {
        this.ssHasWorkflow = true;
      } else {
        this.ssHasWorkflow = false;
      }
    },
    ssName(newValue, oldValue) {
      this.selectedElement = null;
      if (newValue !== oldValue && this.$refs.grid.currentFilter.filters) {
        this.$refs.grid.currentFilter = { filters: [] };
      }
    }
  },
  data() {
    return {
      ssHasWorkflow: false,
      selectedElement: null,
      pageableConfig: { pageSizes: [100, 200, 500], pageSize: 100 },
      viewURL: "",
      viewType: "html",
      viewRawContent: "",
      viewComponent: null,
      viewComponentProps: {},
      gridActions: [
        {
          action: "display",
          title: this.$t("AdminCenterSmartStructure.Display"),
          width: "90%"
        },
        {
          action: "viewProps",
          title: this.$t("AdminCenterSmartStructure.Properties"),
          width: "90%"
        }
      ]
    };
  },
  computed: {
    gridColumns() {
      let columns = [
        {
          field: "title",
          property: true
        },
        {
          title: "Ref. logique",
          field: "name",
          property: true,
          width: "160%"
        },
        {
          title: "Id.",
          field: "initid",
          property: true,
          width: "100%"
        }
      ];
      if (this.ssHasWorkflow === true) {
        columns.push({
          field: "state",
          property: true
        });
      }
      return columns;
    }
  },
  methods: {
    actionClick(event) {
      let seIdentifier;
      switch (event.data.type) {
        case "display":
          seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/view`,
            component: "element-view",
            props: {
              initid: seIdentifier,
              viewId: "!defaultConsultation"
            },
            name: seIdentifier,
            label: seIdentifier
          };
          break;
        case "viewProps":
          seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
          event.preventDefault();
          this.selectedElement = {
            url: `${seIdentifier}/properties`,
            component: "element-properties",
            props: {
              elementId: seIdentifier
            },
            name: seIdentifier,
            label: seIdentifier
          };
          break;
      }
    },
    refreshGrid() {
      this.$refs.grid.refreshGrid(true);
    }
  }
};
