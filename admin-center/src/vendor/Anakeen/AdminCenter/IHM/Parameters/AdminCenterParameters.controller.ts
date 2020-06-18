import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.grid";
import "@progress/kendo-ui/js/kendo.window";
import Component from "vue-class-component";
import { Prop, Mixins, Watch } from "vue-property-decorator";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";

const AdminCenterAllParameters = () => import("./AllParameters/AllParameters.vue");

@Component({
  components: {
    "admin-center-all-parameters": AdminCenterAllParameters,
    AnkTabs,
    AnkTab
  }
})
export default class AdminCenterParametersController extends Mixins(AnkI18NMixin) {
  @Prop({ type: Boolean, default: true })
  public isUserTab!: boolean;

  @Prop({ type: Boolean, default: true })
  public isGlobalTab!: boolean;

  @Prop({ type: Array, default: [] })
  public namespace!: Array<string>;

  @Prop({ type: String, default: "" })
  public specificUser!: string;

  @Prop({ type: String, default: "" })
  public userId!: string;

  @Prop({ type: String, default: "" })
  public paramId!: string;

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
      globalParameters: this.$t("AdminCenterAllParameter.Global parameters"),
      userParameters: this.$t("AdminCenterAllParameter.User parameters")
    };
  }
}
