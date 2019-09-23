import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSEGrid";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement";
import Vue from "vue";
import { Component } from "vue-property-decorator";

@Component({
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-smart-element": SmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterMailController extends Vue {
  public selectMailTemplate(e) {
    switch (e.data.type) {
      case "consultMailTemplate":
        const mailId = e.data.row.name || e.data.row.id.toString();
        // @ts-ignore
        this.$nextTick(() => {
          // @ts-ignore
          this.$refs.mailSmartElement.fetchSmartElement({
            initid: mailId
          });
        });
        break;
    }
  }
}
