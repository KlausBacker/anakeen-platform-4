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
  props: ["ssName"],
  data() {
    return {
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
          title: this.$t("AdminCenterSmartStructure.Display")
        },
        {
          action: "viewProps",
          title: this.$t("AdminCenterSmartStructure.Properties")
        }
      ],
      gridColumns: [
        {
          field: "title",
          property: true
        },
        {
          field: "name",
          property: true
        },
        {
          field: "initid",
          property: true
        }
      ]
    };
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
        case "security":
          if (event.data.row.properties.profid) {
            seIdentifier = event.data.row.properties.name || event.data.row.properties.initid;
            event.preventDefault();
            this.selectedElement = {
              url: `${seIdentifier}/security?profileId=${event.data.row.properties.profid}`,
              component: "element-security",
              props: {
                profileId: event.data.row.properties.profid,
                detachable: true
              },
              name: seIdentifier,
              label: seIdentifier
            };
          }
          break;
      }
    }
  }
};
