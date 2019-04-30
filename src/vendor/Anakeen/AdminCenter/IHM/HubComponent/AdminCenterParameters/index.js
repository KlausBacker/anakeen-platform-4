import ParameterManager from "./AdminCenterParametersEntry.vue";

if (
  window &&
  window.ank &&
  window.ank.hub &&
  window.ank.hub.AdminParametersManager
) {
  window.ank.hub.AdminParametersManager.resolve(
    ParameterManager,
    "ank-admin-parameter"
  );
}
