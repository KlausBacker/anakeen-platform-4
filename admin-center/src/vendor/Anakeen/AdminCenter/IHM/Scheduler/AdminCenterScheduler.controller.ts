import { Component, Mixins } from "vue-property-decorator";
import AnkI18NMixin from "@anakeen/user-interfaces/components/lib/AnkI18NMixin.esm";
import * as $ from "jquery";
import AnkTabs from "@anakeen/user-interfaces/components/lib/AnkTabs.esm";
import AnkTab from "@anakeen/user-interfaces/components/lib/AnkTab.esm";

const AdminCenterTimers = () => import("./Timers/Timers.vue");
const AdminCenterTasks = () => import("./Tasks/Tasks.vue");

@Component({
  components: {
    "admin-center-timers": AdminCenterTimers,
    "admin-center-tasks": AdminCenterTasks,
    AnkTabs,
    AnkTab
  }
})
export default class AdminCenterSchedulerController extends Mixins(AnkI18NMixin) {
  public SelectedSchedulerTab = "TimersTab";

  public get translations() {
    return {
      timersTab: this.$t("AdminCenterSchedulerTranslation.Timers"),
      tasksTab: this.$t("AdminCenterSchedulerTranslation.Tasks")
    };
  }
}
