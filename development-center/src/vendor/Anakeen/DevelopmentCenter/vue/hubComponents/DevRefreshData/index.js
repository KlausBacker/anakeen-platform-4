import RefreshData from "./DevRefreshData.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-refresh-data"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-refresh-data"].resolve(RefreshData, "ank-dev-refresh-data");
}
