import HubBusinessAppEntry from "../IHM/HubComponent/HubBusinessApp.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-business-app"]
) {
  // @ts-ignore
  window.ank.hub["ank-business-app"].resolve(HubBusinessAppEntry, "ank-business-app");
}
