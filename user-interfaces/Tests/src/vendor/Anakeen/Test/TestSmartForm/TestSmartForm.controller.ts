/* tslint:disable:object-literal-sort-keys no-console */
import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";
import * as example from "./SmartFormExample1.json";

import VJsoneditor from "v-jsoneditor/src/index";

import Vue from "vue";
import Component from "vue-class-component";

// noinspection JSUnusedGlobalSymbols
@Component({
  components: {
    VJsoneditor,
    "ank-smart-form": AnkSmartForm,
    "ank-splitter": AnkSplitter
  }
})
export default class TestSmartFormController extends Vue {
  public json: object = example;
  public options: any = {
    mode: "text"
  };

  public panes: object[] = [
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "30%"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "70%"
    }
  ];

  public $refs!: {
    smartFormRef: any;
    smartFormSplitter: any;
  };

  // noinspection JSMethodCanBeStatic
  public onError(e) {
    console.error(e);
  }

  public toggleMode() {
    this.options.mode="code";
  }

  public mounted() {
    // @ts-ignore
    this.$refs.smartFormSplitter.disableEmptyContent();
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
