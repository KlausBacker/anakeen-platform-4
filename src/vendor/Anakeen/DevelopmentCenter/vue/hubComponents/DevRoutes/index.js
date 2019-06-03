import Routes from "./DevRoutes.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-routes"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-routes"].resolve(Routes, "ank-dev-routes");
}
