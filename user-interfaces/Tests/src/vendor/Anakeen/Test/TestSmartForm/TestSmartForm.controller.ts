import AnkSplitter from "@anakeen/internal-components/lib/Splitter";
import AnkSmartForm from "@anakeen/user-interfaces/components/lib/AnkSmartForm";

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
  public json: object = {
    renderOptions: {},
    structure: {
      smartSet: {
        childs: [
          {
            smartText: {
              label: "Title",
              name: "my_title",
            }
          }
        ],
        label: "My first frame",
        name: "my_firstframe"
      }
    },

  };
  public options: object = {};

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

  // noinspection JSMethodCanBeStatic
  public onError(e) {
    console.error(e);
  }

  public mounted() {
    // @ts-ignore
    this.$refs.smartFormSplitter.disableEmptyContent();
  }
}
