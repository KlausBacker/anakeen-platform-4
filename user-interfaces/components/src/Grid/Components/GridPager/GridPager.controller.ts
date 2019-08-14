import "@progress/kendo-ui/js/kendo.pager";
import Vue from "vue";
import { Component, Prop, Watch } from "vue-property-decorator";

@Component({
  name: "ank-se-grid-pager"
})
export default class GridPagerController extends Vue {
  public pager = null;
  @Prop({
    default: null,
    type: Object
  })
  public gridComponent;

  @Watch("gridComponent")
  public watchGridComponent(newValue) {
    if (newValue) {
      this.gridComponent = newValue;
      this.initGridComponent();
    }
  }

  public initGridComponent() {
    this.pager = $(this.$refs.pager)
      .kendoPager({
        dataSource: this.gridComponent.dataSource,
        pageSizes: this.gridComponent.pageSizes
      })
      .data("kendoPager");
    this.gridComponent.kendoGrid.bind("dataBound", () => this.refreshPager());
  }

  private refreshPager() {
    this.pager.refresh();
  }
}
