import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import { Component, Vue } from "vue-property-decorator";

@Component({
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-smart-element": () => SmartElement,
    "ank-split-panes": AnkPaneSplitter
  }
})
export default class AdminCenterMailController extends Vue {
  public selectedMail: string = "";
  public selectMailTemplate(e) {
    switch (e.data.type) {
      case "consultMailTemplate":
        this.selectedMail = e.data.row.name || e.data.row.id.toString();
        this.$nextTick(() => {
          // @ts-ignore
          this.$refs.mailSmartElement.fetchSmartElement({
            initid: this.selectedMail
          });
        });
        break;
    }
  }
}
