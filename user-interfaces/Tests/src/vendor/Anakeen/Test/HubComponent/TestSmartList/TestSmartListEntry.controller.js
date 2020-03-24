import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
import AnkSmartListVue from "@anakeen/user-interfaces/components/lib/AnkSmartElementList.esm";
import AnkGridExpandButtonController from "@anakeen/user-interfaces/components/lib/AnkSmartElementGridExpandButton.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import VJsonEditor from "v-jsoneditor";

export default {
  name: "ank-test-smart-list",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    AnkPaneSplitter,
    "ank-smart-element-list": AnkSmartListVue,
    "ank-se-grid-expand-button": AnkGridExpandButtonController,
    VJsonEditor
  },
  watch: {
    selected(newValue, oldValue) {
      console.log(newValue, oldValue);
    }
  },
  data() {
    return {
      hasError: false,
      hasWarning: false,
      tooltip: "",
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      options: {
        mode: "code",
        // schema: jsonSchema,
        onChangeText: () => {
          try {
            this.$refs.jsonEditorRef.editor.get();
            this.hasError = false;
          } catch (e) {
            this.hasError = true;
            this.tooltip = e.message;
            this.$refs.jsonEditorRef.$emit("error", e.message);
          }
        }
      },
      selected: 1661,
      listConfig: {
        smartCollection: "DEVBILL",
        selectable: true,
        attachedData: [
          {
            field: "bill_title"
          },
          {
            field: "mdate",
            property: true
          }
        ],
        label: "Ma liste personnalisée",
        autoFit: true,
        contentUrl: "/api/v2/grid/controllers/{controller}/{op}/{collection}",
        controller: "DEFAULT_GRID_CONTROLLER"
      }
    };
  },
  methods: {
    onError(errorMsg) {
      if (errorMsg) {
        this.tooltip = errorMsg;
        this.hasError = true;
      }
    }
  }
};