import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";
import WflData from "./WorkflowData/WorkflowData.vue";
import WflList from "./WorkflowList/WorkflowList.vue";

@Component({
  components: {
    "ank-splitter": Splitter,
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
  public panes: any = [
    {
      collapsible: true,
      resizable: true,
      scrollable: false,
      size: "500px"
    },
    {
      collapsible: true,
      resizable: true,
      scrollable: false
    }
  ];

  @Watch("selectedWfl")
  public watchSelectedWfl(newValue) {
    if (newValue) {
      this.isEmpty = false;
    }
  }

  @Watch("isEmpty")
  public watchIsEmpty(newValue) {
    if (!newValue) {
      this.$nextTick(() => {
        // @ts-ignore
        this.$refs.wflSplitter.disableEmptyContent();
      });
    } else {
      // @ts-ignore
      this.$refs.wflSplitter.enableEmptyContent();
    }
  }

  public get getGraphUrl() {
    return `/api/v2/admin/workflows/image/${this.selectedWfl}.svg?orientation=${this.wflOrient}&useLabel=${this.wflUseLabel}&forceUpdate=${this.force}`;
  }
}
