import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkSEVueGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import { Component, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

@Component({
  components: {
    "ank-smart-element": (): Promise<unknown> => SmartElement,
    "ank-split-panes": AnkPaneSplitter,
    "ank-se-vue-grid": AnkSEVueGrid
  }
})
export default class AdminCenterMailController extends Mixins(AnkI18NMixin) {
  public $refs!: {
    mailSmartElement: SmartElement;
    grid: AnkSEVueGrid;
  };
  public actions: object[] = [{ action: "consultMailTemplate", title: "Display" }];
  public columns: object[] = [
    { field: "id", property: true, hidden: true, title: "Identification", withContext: false },
    { field: "tmail_title", title: `Title`, withContext: false },
    { field: "tmail_subject", title: `Subject`, withContext: false }
  ];
  public selectedMail = "";
  public pageable = {
    buttonCount: 0,
    pageSize: 50,
    pageSizes: [50, 100, 200]
  };

  public mounted(): void {
    this.columns = [
      { field: "id", property: true, hidden: true, title: "Identification", withContext: false },
      { field: "tmail_title", title: `${this.$t("AdminCenterMail.Title")}`, withContext: false },
      { field: "tmail_subject", title: `${this.$t("AdminCenterMail.Subject")}`, withContext: false }
    ];
  }

  public selectMailTemplate(e): void {
    switch (e.data.type) {
      case "consultMailTemplate":
        this.selectedMail = e.data.row.properties.id.toString();

        this.$refs.grid.selectedRows = [this.selectedMail];
        this.$nextTick(() => {
          this.$refs.mailSmartElement.fetchSmartElement({
            initid: this.selectedMail
          });
        });
        break;
    }
  }
}
