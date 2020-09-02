import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import SmartElement from "@anakeen/user-interfaces/components/lib/AnkSmartElement.esm";
import AnkSEVueGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import { Component, Mixins, Vue } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";

Vue.use(ButtonsInstaller);

@Component({
  components: {
    "ank-smart-element": (): Promise<unknown> => SmartElement,
    "ank-split-panes": AnkPaneSplitter,
    "ank-se-vue-grid": AnkSEVueGrid
  }
})
export default class AdminCenterRenderDescriptionController extends Mixins(AnkI18NMixin) {
  public $refs!: {
    rdSmartElement: SmartElement;
    rdExample: SmartElement;
    grid: AnkSEVueGrid;
    rdSplitter: AnkPaneSplitter;
  };
  public actions: object[] = [{ action: "consultRenderDescription", title: "Display" }];
  public columns: object[] = [
    { field: "id", property: true, hidden: true, title: "Identification" },
    { field: "rd_title" },
    { field: "rd_famid" },
    { field: "rd_mode" },
    { field: "rd_lang" }
  ];
  public selectedRenderDescription = "";
  public selectedExample = "";
  public pageable = {
    buttonCount: 0,
    pageSize: 50,
    pageSizes: [50, 100, 200]
  };
  public panelSizes = {
    grid: 50,
    description: 50,
    example: 0
  };

  public selectRenderDescription(e): void {
    switch (e.data.type) {
      case "consultRenderDescription":
        this.selectedRenderDescription = e.data.row.properties.id.toString();
        this.selectedExample = "";
        this.$refs.grid.selectedRows = [this.selectedRenderDescription];
        this.$nextTick(() => {
          this.$refs.rdSmartElement.fetchSmartElement({
            initid: this.selectedRenderDescription,
            viewId: "!defaultConsultation"
          });
        });
        break;
    }
  }

  public afterSaveRefreshGrid(): void {
    this.$refs.grid.refreshGrid(true);
    if (this.$refs.rdExample.isLoaded() && this.$refs.rdExample.getProperty("initid")) {
      this.$refs.rdExample.reinitSmartElement();
    }
  }

  public createNewDescription(): void {
    this.selectedRenderDescription = "-";
    this.$refs.grid.selectedRows = [];
    this.$refs.rdSmartElement.fetchSmartElement({ initid: "RENDERDESCRIPTION", viewId: "!defaultCreation" });
  }

  public updateExample(event, smartElement, params): void {
    if (params.eventId === "document.load") {
      if (params.attrid === "rd_example" || (params.target && params.target.classList.contains("rd-example"))) {
        event.preventDefault();
        const initid = params.options[0];
        const descMode = this.$refs.rdSmartElement.getValue("rd_mode");
        let viewId = "!defaultConsultation";

        if (descMode && descMode.length === 1 && descMode[0].value === "edit") {
          viewId = "!defaultEdition";
        }
        this.selectedExample = initid;
        this.$refs.rdExample.fetchSmartElement({ initid: initid, viewId: viewId });

        this.resizePanel();
      }
    }
  }

  protected resizePanel(): void {
    this.panelSizes = {
      grid: this.$refs.rdSplitter.panes[0].width,
      description: this.$refs.rdSplitter.panes[1].width,
      example: this.$refs.rdSplitter.panes[2].width
    };

    if (this.panelSizes.example === 0) {
      this.panelSizes = {
        grid: 30,
        description: 35,
        example: 35
      };
      this.$refs.rdSplitter.panes[0].width = this.panelSizes.grid;
      this.$refs.rdSplitter.panes[1].width = this.panelSizes.description;
      this.$refs.rdSplitter.panes[2].width = this.panelSizes.example;
    }
  }
}
