import ParameterManager from "./AdminCenterParametersEntry.vue";

if (
  window &&
  window.ank &&
  window.ank.hub &&
  window.ank.hub["ank-admin-parameter"]
) {
  window.ank.hub["ank-admin-parameter"].resolve(
    ParameterManager,
    "ank-admin-parameter"
  );
}
