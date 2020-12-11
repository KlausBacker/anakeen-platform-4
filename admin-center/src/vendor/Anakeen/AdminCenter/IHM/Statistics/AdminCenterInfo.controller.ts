import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import { Component, Mixins, Prop, Watch } from "vue-property-decorator";
import ActiveUsers from "./ActiveUsers/ActiveUsers.vue";
import About from "./About/About.vue";

@Component({
  components: {
    AnkTabs,
    AnkTab,
    ActiveUsers,
    About
  }
})
export default class AdminCenterInfoController extends Mixins(AnkI18NMixin) {
  @Prop({ type: String, default: "about" })
  public selectedTab!: string;

  @Watch("selectedTab")
  protected watchSelectedTab(newValue): void {
    if (newValue) {
      this.mySelectedTab = newValue;
    }
  }
  public mySelectedTab = this.selectedTab;

  protected handleNotification(typeNotification, message): void {
    this.$emit("notify", typeNotification, message);
  }
  public get translations() {
    return {
      activeUsers: this.$t("AdminCenterInfos.Active users"),
      about: this.$t("AdminCenterInfos.About")
    };
  }
}
