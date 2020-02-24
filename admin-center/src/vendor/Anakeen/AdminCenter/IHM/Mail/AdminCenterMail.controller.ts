import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkSEVueGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementVueGrid.esm";
import { Component, Vue } from "vue-property-decorator";

@Component({
  components: {
    "ank-smart-element": () => SmartElement,
    "ank-split-panes": AnkPaneSplitter,
    "ank-se-vue-grid": AnkSEVueGrid
  }
})
export default class AdminCenterMailController extends Vue {
  public actions: object[] = [{ action: "consultMailTemplate", title: "Display" }];
  public columns: object[] = [
    { field: "id", property: true, hidden: true, title: "Identification", withContext: false },
    { field: "tmail_title", title: "Title", withContext: false },
    { field: "tmail_subject", title: "Subject", withContext: false }
  ];
  public selectedMail = "";

  public selectMailTemplate(e) {
    switch (e.data.type) {
      case "consultMailTemplate":
        this.selectedMail = e.data.row.properties.id.toString();
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
