import Mail from "./AdminCenterRenderDescriptionEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-render-description-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-render-description-manager"].resolve(Mail, "ank-admin-render-description-manager");
}
