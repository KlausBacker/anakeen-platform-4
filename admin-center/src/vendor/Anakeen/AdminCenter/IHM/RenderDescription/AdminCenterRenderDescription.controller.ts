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
export default class AdminCenterRenderDescriptionController extends Mixins(AnkI18NMixin) {
  public $refs!: {
    rdSmartElement: SmartElement;
  };
  public actions: object[] = [{ action: "consultRenderDescription", title: "Display" }];
  public columns: object[] = [
    { field: "id", property: true, hidden: true, title: "Identification" },
    { field: "rd_title" },
    { field: "rd_famid" },
    { field: "rd_mode" },
    { field: "rd_langs" }
  ];
  public selectedRenderDescription = "";
  public pageable = {
    buttonCount: 0,
    pageSize: 50,
    pageSizes: [50, 100, 200]
  };

  public mounted(): void {}

  public selectRenderDescription(e): void {
    switch (e.data.type) {
      case "consultRenderDescription":
        this.selectedRenderDescription = e.data.row.properties.id.toString();
        this.$nextTick(() => {
          this.$refs.rdSmartElement.fetchSmartElement({
            initid: this.selectedRenderDescription
          });
        });
        break;
    }
  }
}
