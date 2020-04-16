import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkSEVueGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import { Component, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

@Component({
  components: {
    "ank-smart-element": () => SmartElement,
    "ank-split-panes": AnkPaneSplitter,
    "ank-se-vue-grid": AnkSEVueGrid
  }
})
export default class AdminCenterMailController extends Mixins(AnkI18NMixin) {
  public actions: object[] = [{ action: "consultMailTemplate", title: "Display" }];
  public columns: object[] = [
    { field: "id", property: true, hidden: true, title: "Identification", withContext: false },
    { field: "tmail_title", title: "Title", withContext: false },
    { field: "tmail_subject", title: `Subject`, withContext: false }
  ];
  public selectedMail = "";

  public mounted(): void {
    this.columns = [
      { field: "id", property: true, hidden: true, title: "Identification", withContext: false },
      { field: "tmail_title", title: "Title", withContext: false },
      { field: "tmail_subject", title: `${this.$t("AdminCenterMail.Subject")}`, withContext: false }
    ];
  }

  public selectMailTemplate(e): void {
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
