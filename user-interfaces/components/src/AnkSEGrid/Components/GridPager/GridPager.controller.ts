import "@progress/kendo-ui/js/kendo.pager";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import I18nMixin from "../../../../mixins/AnkVueComponentMixin/I18nMixin";
@Component({
  name: "ank-se-grid-pager"
})
export default class GridPagerController extends Mixins(I18nMixin) {
  public pager = null;
  @Prop({
    default: null,
    type: Object
  })
  public gridComponent;

  public get serverTranslations() {
    if (this.gridComponent && this.gridComponent.translations && this.gridComponent.translations.pageable) {
      return this.gridComponent.translations.pageable;
    }
    return {};
  }

  public get translations() {
    return {
      display: this.serverTranslations.display || (this.$t("gridPager.{0} - {1} of {2} items") as string),
      empty: this.serverTranslations.empty || (this.$t("gridPager.No items to display") as string),
      itemsPerPage: this.serverTranslations.itemsPerPage || (this.$t("gridPager.items per page") as string),
      of: this.serverTranslations.of || (this.$t("gridPager.of {0}") as string)
    };
  }

  @Watch("gridComponent")
  public watchGridComponent(newValue) {
    if (newValue) {
      this.initGridComponent(newValue);
    }
  }

  public initGridComponent(gridComponent) {
    this.pager = $(this.$refs.pager)
      .kendoPager({
        dataSource: gridComponent.dataSource,
        messages: this.translations,
        pageSizes: gridComponent.pageSizes,
        refresh: gridComponent.refresh
      })
      .data("kendoPager");
    gridComponent.kendoGrid.bind("dataBound", () => this.refreshPager());
  }

  private refreshPager() {
    this.pager.refresh();
  }
}
