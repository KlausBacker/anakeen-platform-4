import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.filtercell";
import "@progress/kendo-ui/js/kendo.grid.js";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";

@Component({
  components: {
    AnkTabs,
    AnkTab
  }
})
export default class AdminCenterStatisticsController extends Mixins(AnkI18NMixin) {
  @Prop({ type: String, default: "" })
  public selectedTab!: string;

  @Watch("selectedTab")
  protected watchSelectedTab(newValue): void {
    if (newValue) {
      this.mySelectedTab = newValue;
    }
  }
  public mySelectedTab = this.selectedTab;

  protected onNavigate(route): void {
    this.$emit("navigate", route);
  }

  protected handleNotification(typeNotification, message): void {
    this.$emit("notify", typeNotification, message);
  }
  public get translations() {
    return {
      activeUsers: this.$t("AdminCenterStatistics.Active users")
    };
  }
}
