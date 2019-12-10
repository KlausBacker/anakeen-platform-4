import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import { Component, Vue, Watch } from "vue-property-decorator";
import WflData from "./WorkflowData/WorkflowData.vue";
import WflList from "./WorkflowList/WorkflowList.vue";

@Component({
  components: {
    "ank-split-panes": AnkPaneSplitter,
    "wfl-data": WflData,
    "wfl-list": WflList
  }
})
export default class AdminCenterWorkflowController extends Vue {
  public wflOrient: string = "TB";
  public wflUseLabel: string = "activity";
  public isEmpty: boolean = true;
  public selectedWfl: string = "";
  public force: number = 0;

  @Watch("selectedWfl")
  public watchSelectedWfl(newValue) {
    if (newValue) {
      this.isEmpty = false;
    }
  }

  public get getGraphUrl() {
    return `/api/v2/admin/workflows/image/${this.selectedWfl}.svg?orientation=${this.wflOrient}&useLabel=${
      this.wflUseLabel
    }&forceUpdate=${this.force}`;
  }
}
