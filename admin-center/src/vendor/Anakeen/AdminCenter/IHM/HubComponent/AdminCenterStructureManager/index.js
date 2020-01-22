import SmartStructureManager from "./AdminCenterStructureEntry.vue";

if (window && window.ank && window.ank.hub && window.ank.hub["ank-admin-structure"]) {
  window.ank.hub["ank-admin-structure"].resolve(SmartStructureManager, "ank-admin-structure");
}
