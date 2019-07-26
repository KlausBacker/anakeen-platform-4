/* tslint:disable:object-literal-sort-keys no-console */
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import * as jsonSchema from "./SmartForm.schema.json";
import * as example from "./SmartFormExample2.json";

import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import VJsoneditor from "v-jsoneditor";

import { Component, Vue, Watch } from "vue-property-decorator";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    VJsoneditor,
    "ank-smart-form": AnkSmartForm,
    "ank-splitter": AnkSplitter,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class TestSmartFormController extends Vue {
  public json: object = example;
  public hasWarning: boolean = false;
  public hasError: boolean = false;
  public options: any = {
    mode: "code",
    schema: jsonSchema,
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
  };

  public $refs!: {
    smartFormRef: any;
    smartFormSplitter: any;
    jsonEditorRef: any;
  };
  public tooltip: string = "";

  @Watch("json", { immediate: true, deep: true })
  public onJsonChanged() {
    this.hasError = false;

    // Need to wait because no found event on ace editor to get annotations update after changed
    window.setTimeout(() => {
      const warnings = this.$refs.jsonEditorRef.editor.annotations;
      this.hasWarning = warnings && warnings.length > 0;
      this.tooltip = "";
      if (this.hasWarning) {
        const msgWarnings = [];
        warnings.forEach(item => {
          msgWarnings.push(item.text);
        });
        this.tooltip = msgWarnings.join(" \n");
      }
    }, 1000);
  }

  public onError(errorMsg) {
    if (errorMsg) {
      this.tooltip = errorMsg;
      this.hasError = true;
    }
  }
  public mounted() {
    // @ts-ignore
    this.$nextTick(() => {
      this.$refs.smartFormRef.addEventListener("ready", (event, data) => {
        console.log("ready", event, data);
      });
      this.$refs.smartFormRef.addEventListener("change", (event, data) => {
        console.log("change", event, data);
      });

      this.$refs.smartFormRef.addEventListener("beforeSave", (event, data) => {
        console.log("change", event, data);
      });
      this.$refs.smartFormRef.addEventListener("actionClick", (event, data, options) => {
        console.log("action", options.eventId);
      });
    });
  }
}
