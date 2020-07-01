import { Component, Prop, Mixins } from "vue-property-decorator";
import VueI18n from "vue-i18n";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
// import * as $ from "jquery";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";
import AnkPaneSplitter from "@anakeen/internal-components/lib/PaneSplitter";
import "@progress/kendo-ui/js/kendo.grid";

const AdminCenterIncomingTimers = () => import("./IncomingTimers/IncomingTimers.vue");
const AdminCenterPreviousTimers = () => import("./PreviousTimers/PreviousTimers.vue");

@Component({
  components: {
    AnkTabs,
    AnkTab,
    "ank-split-panes": AnkPaneSplitter,
    "incoming-timer": AdminCenterIncomingTimers,
    "previous-timer": AdminCenterPreviousTimers
  }
})
export default class AdminCenterTimersController extends Mixins(AnkI18NMixin) {
  @Prop({ type: Boolean, required: true })
  public timerTab!: boolean;

  // public $refs!: {
  //   [key: string]: any;
  // };
  // public kendoGridFutureAction: any = null;
  public mySelectedTab = "incomingTimerActions";

  public get translations(): { [key: string]: VueI18n.TranslateResult } {
    return {
      incomingActions: this.$t("AdminCenterTimersTranslation.Incoming actions"),
      previousActions: this.$t("AdminCenterTimersTranslation.Previous actions")
    };
  }
}
