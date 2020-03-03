import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
import AnkSmartGridVue from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import GridExportButton from "@anakeen/user-interfaces/components/lib/AnkSmartElementGridExportButton.esm";
import AnkGridExpandButtonController from "@anakeen/user-interfaces/components/lib/AnkSmartElementGridExpandButton.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import VJsonEditor from "v-jsoneditor";
// import * as jsonSchema from "../../TestSmartForm/SmartForm.schema";

export default {
  name: "ank-test-smart-grid",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    AnkPaneSplitter,
    "ank-smart-element-grid": AnkSmartGridVue,
    "export-button": GridExportButton,
    "ank-se-grid-expand-button": AnkGridExpandButtonController,
    VJsonEditor
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
      gridConfig: {
        collection: "TST_DDUI_ALLTYPE",
        columns: [
          { field: "initid", property: true },
          { field: "state", property: true },
          { field: "title", property: true },
          // { field: "test_ddui_all__title" },
          { field: "test_ddui_all__longtext" },
          // { field: "test_ddui_all__htmltext" },
          { field: "test_ddui_all__account_multiple" },
          { field: "test_ddui_all__account_multiple_array" },
          // { field: "test_ddui_all__enumlist" },
          { field: "test_ddui_all__docid" },
          // { field: "test_ddui_all__account" },
          { field: "test_ddui_all__date" },
          { field: "test_ddui_all__color" },
          // { field: "test_ddui_all__image" },
          { field: "test_ddui_all__timestamp" },
          { field: "test_ddui_all__money" }
        ],
        actions: [
          { action: "display", title: "Display" },
          { action: "modify", title: "Modify", iconClass: "fa fa-edit" },
          { action: "delete", title: "Delete", iconClass: "fa fa-trash" }
        ],
        defaultExportButton: true,
        defaultExpandable: true,
        defaultShownColumns: true,
        checkable: true,
        reorderable: true,
        resizable: true
      }
    };
  },
  watch: {
    gridProps(newValue, oldValue) {
      if (newValue !== oldValue) {
        try {
          const config = JSON.parse(newValue);
          Object.keys(config).forEach(key => {
            if (this.gridConfig[key] !== config[key]) {
              this.gridConfig[key] = config[key];
            }
          });
        } catch (err) {
          console.warn("normal error:", err);
        }
      }
    }
  },
  created() {
    this.gridProps = JSON.stringify(this.gridConfig, null, 2);
  },
  methods: {
    onRowActionClick(evt) {},
    onError(errorMsg) {
      if (errorMsg) {
        this.tooltip = errorMsg;
        this.hasError = true;
      }
    }
  }
};