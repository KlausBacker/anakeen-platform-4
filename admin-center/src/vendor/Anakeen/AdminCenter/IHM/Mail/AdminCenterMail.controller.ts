import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import Vue from "vue";
import { Component } from "vue-property-decorator";

@Component({
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-smart-element": SmartElement,
    "ank-splitter": Splitter
  }
})
export default class AdminCenterMailController extends Vue {
  public panes: any = [
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "50%"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "50%"
    }
  ];

  public selectMailTemplate(e) {
    switch (e.data.type) {
      case "consultMailTemplate":
        e.preventDefault();
        const mailId = e.data.row.name || e.data.row.id.toString();
        // @ts-ignore
        this.$refs.mailSplitter.disableEmptyContent();
        const cb = () => {
          // @ts-ignore
          this.$refs.mailSmartElement.fetchSmartElement({
            initid: mailId
          });
        };
        this.$nextTick(() => {
          // @ts-ignore
          if (this.$refs.mailSmartElement.isLoaded()) {
            cb();
          } else {
            // @ts-ignore
            this.$refs.mailSmartElement.$once("documentLoaded", cb);
          }
        });
        break;
    }
  }
}
