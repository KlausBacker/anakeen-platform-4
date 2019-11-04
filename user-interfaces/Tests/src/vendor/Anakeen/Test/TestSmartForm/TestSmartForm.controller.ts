/* eslint-disable no-console */
/* eslint-disable no-unused-vars */

/* tslint:disable:object-literal-sort-keys no-console */

import AnkSmartForm, { ISmartFormConfiguration } from "../../../../../../components/lib/AnkSmartForm.esm";
import * as jsonSchema from "./SmartForm.schema.json";
import SmartFormExamples from "./TestExamplesSmartForm.vue";

import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import VJsoneditor from "v-jsoneditor";

import { Component, Vue, Watch } from "vue-property-decorator";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    VJsoneditor,
    SmartFormExamples,
    "ank-smart-form": AnkSmartForm,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class TestSmartFormController extends Vue {
  public json: ISmartFormConfiguration = {};
  public localIndex: number = -1;
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
    smartFormRef: AnkSmartForm;
    smartFormSplitter: any;
    jsonEditorRef: any;
    smartExampleRef: any;
  };
  public tooltip: string = "";
  private initialSet: boolean = true;

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

    if (this.localIndex >= 0) {
      if (!this.initialSet) {
        this.$refs.smartExampleRef.recordExample(this.localIndex, this.json);
      }
      this.initialSet = false;
    }
  }

  public setJson({ json = {}, localIndex = -1 }) {
    this.localIndex = localIndex;
    this.json = json;
    this.initialSet = true;
  }
  public recordNewExample() {
    this.$refs.smartExampleRef.createExample();
  }

  public onError(errorMsg) {
    if (errorMsg) {
      this.tooltip = errorMsg;
      this.hasError = true;
    }
  }
  public mounted() {
    this.$refs.smartFormRef.$on("ready", (event, data) => {
      console.log("ready", event, data);
    });
    /*
    this.$refs.smartFormRef.$on("smartFieldChange", (event, data) => {
      console.log("change", event, data);
    });

     */

    this.$refs.smartFormRef.$on("beforeSave", (event, data) => {
      console.log("beforeSave", event, data);
    });

    this.$refs.smartFormRef.$on("actionClick", (event, data, options) => {
      console.log("action", options.eventId);
    });

    this.$refs.smartExampleRef.selectExample(0);
  }
}
