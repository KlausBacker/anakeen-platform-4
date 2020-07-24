import SchedulerManager from "./AdminCenterSchedulerManagerEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-scheduler-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-scheduler-manager"].resolve(SchedulerManager, "ank-admin-scheduler-manager");
}
