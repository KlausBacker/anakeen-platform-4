import I18n from "./AdminCenterI18nEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-i18n"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-i18n"].resolve(I18n, "ank-admin-i18n");
}
