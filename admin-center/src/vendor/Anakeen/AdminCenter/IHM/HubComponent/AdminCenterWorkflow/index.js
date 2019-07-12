import WorkflowManager from "./AdminCenterWorkflowManagerEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-workflow-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-workflow-manager"].resolve(
    WorkflowManager,
    "ank-admin-workflow-manager"
  );
}
