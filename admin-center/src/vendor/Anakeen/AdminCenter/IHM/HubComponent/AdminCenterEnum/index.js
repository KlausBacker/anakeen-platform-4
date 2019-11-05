import Enum from "./AdminCenterEnumEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-admin-enum-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-admin-enum-manager"].resolve(Enum, "ank-admin-enum-manager");
}
