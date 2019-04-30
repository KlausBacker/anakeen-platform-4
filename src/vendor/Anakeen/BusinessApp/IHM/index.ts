import HubBusinessAppEntry from "../IHM/HubComponent/HubBusinessApp.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub.HubBusinessApp
) {
  // @ts-ignore
  window.ank.hub.HubBusinessApp.resolve(
    HubBusinessAppEntry,
    "ank-business-app"
  );
}
