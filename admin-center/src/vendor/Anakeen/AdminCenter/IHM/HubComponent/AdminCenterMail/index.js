import Mail from "./AdminCenterMailEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-mail-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-mail-manager"].resolve(Mail, "ank-admin-mail-manager");
}
