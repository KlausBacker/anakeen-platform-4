import Splitter from "@anakeen/internal-components/lib/Splitter.js";
import { ButtonsInstaller } from "@progress/kendo-buttons-vue-wrapper";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import "@progress/kendo-ui/js/kendo.toolbar.js";
import Vue from "vue";
import { Component, Watch } from "vue-property-decorator";
import WflData from "./WorkflowData/WorkflowData.vue";
import WflList from "./WorkflowList/WorkflowList.vue";

Vue.use(ButtonsInstaller);
declare var kendo;
@Component({
  components: {
    "ank-splitter": Splitter,
    "wfl-data": WflData,
    "wfl-list": WflList
  }
})
export default class AdminCenterWorkflowController extends Vue {
  public wflList;
  public wflData = {};
  public wflGraph: string = "";
  public wflOrient: string = "TB";
  public wflUseLabel: string = "activity";
  public wflName: string = "";
  public isEmpty: boolean = true;
  public selectedWfl: string = "";
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

  @Watch("wflName")
  public watchWflName(newValue) {
    this.selectedWfl = newValue;
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
  public reloadGraph() {
    this.displayGraph();
  }
  public getGraphUrl() {
    return `/api/v2/admin/workflows/image/${this.wflName}.svg?orientation=${this.wflOrient}&useLabel=${this.wflUseLabel}`;
  }

  public displayGraph() {
    this.$http.get(this.getGraphUrl()).then(response => {
      this.wflGraph = response.data;
    });
  }

  public onItemClicked(item) {
    this.isEmpty = false;
    this.wflName = item.id;
    this.selectedWfl = this.wflName;
    this.$http
      .get(`/api/v2/admin/workflow/data/${this.wflName}`)
      .then(response => {
        this.wflData = response.data.data.properties;
      });
    setTimeout(() => {
      this.displayGraph();
    }, 300);

    $(window).resize();
  }

  public onListReady(data) {
    this.wflList = data;
  }
}
