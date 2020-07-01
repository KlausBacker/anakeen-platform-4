import { Component, Mixins, Prop } from "vue-property-decorator";
import VueI18n from "vue-i18n";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
// import * as $ from "jquery";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";

const AdminCenterIncomingTasks = () => import("./IncomingTasks/IncomingTasks.vue");
const AdminCenterPreviousTasks = () => import("./PreviousTasks/PreviousTasks.vue");

@Component({
  components: {
    AnkTabs,
    AnkTab,
    "incoming-task": AdminCenterIncomingTasks,
    "previous-task": AdminCenterPreviousTasks
  }
})
export default class AdminCenterTasksController extends Mixins(AnkI18NMixin) {
  @Prop({ type: Boolean, required: true })
  public timerTab!: boolean;

  public mySelectedTab = "incomingTaskActions";

  public get translations(): { [key: string]: VueI18n.TranslateResult } {
    return {
      incomingActions: this.$t("AdminCenterTasksTranslation.Incoming actions"),
      previousActions: this.$t("AdminCenterTasksTranslation.Previous actions")
    };
  }
}
