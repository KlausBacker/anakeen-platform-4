import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartCriteriaExample from "./TestExamplesFulltextSmartCriteria.vue";
import VJsonEditor from "v-jsoneditor";
import AnkFulltextSmartCriteria from "../../../../../../../components/lib/AnkFulltextSmartCriteria.esm";
import AnkSmartElementGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";

export default {
  name: "ank-test-fulltext-smart-criteria",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    AnkPaneSplitter,
    "ank-fulltext-smart-criteria": AnkFulltextSmartCriteria,
    "smart-criteria-examples": SmartCriteriaExample,
    "ank-smart-grid": AnkSmartElementGrid,
    VJsonEditor
  },
  data() {
    return {
      hasError: false,
      hasWarning: false,
      smartFilter: {
        filters: []
      },
      tooltip: "",
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      options: {
        mode: "code",
        // schema: jsonSchema,
        onChangeText: () => {
          try {
            const result = this.$refs.jsonEditorRef.editor.get();
            if (result.responsiveColumns) {
              this.responsiveColumns = result.responsiveColumns;
            }
            this.hasError = false;
          } catch (e) {
            this.hasError = true;
            this.tooltip = e.message;
            this.$refs.jsonEditorRef.$emit("error", e.message);
          }
        }
      },
      criteriaConfig: {},
      gridColumns: [
        { field: "title", property: true },
        { field: "state", property: true },
        { field: "bill_clients", title: "Clients" },
        { field: "bill_billdate" },
        { field: "bill_cost" }
      ],
      responsiveColumns: []
    };
  },
  created() {
    this.criteriaProps = JSON.stringify(this.criteriaConfig, null, 2);
  },
  methods: {
    recordNewExample() {
      this.$refs.smartExampleRef.createExample();
    },
    setConfig(configObject) {
      this.localIndex = configObject.localIndex ? configObject.localIndex : -1;
      this.criteriaConfig = configObject.config ? configObject.config : {};

      this.responsiveColumns = configObject.responsiveColumns ? configObject.responsiveColumns : [];
      this.initialSet = true;
    },
    onError(errorMsg) {
      if (errorMsg) {
        this.tooltip = errorMsg;
        this.hasError = true;
      }
    },
    initCriteriaExample() {
      this.$refs.smartExampleRef.selectExample(0);
    },

    testSmartCriteriaReady(...args) {
      console.log("smartCriteriaReady : ", args);
      const smartForm = this.$refs.smartCriteria.getSmartCriteriaForm();
    },
    testSmartCriteriaChange(...args) {
      console.log("SmartCriteriaChange : ", args);
    },
    testSmartCriteriaError(...args) {
      console.log("SmartCriteriaError : ", args);
    },

    onCriteriaValidated() {
      const filterValue = this.$refs.smartCriteria.getFilters();
      console.log(JSON.stringify(filterValue));
      this.smartFilter = filterValue;
    }
  }
};
