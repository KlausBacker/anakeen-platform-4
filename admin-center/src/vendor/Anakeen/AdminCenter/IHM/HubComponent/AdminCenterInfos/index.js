import Statistics from "./AdminCenterInfosEntry.vue";

if (window && window.ank && window.ank.hub && window.ank.hub["ank-admin-infos"]) {
  window.ank.hub["ank-admin-infos"].resolve(Statistics, "ank-admin-infos");
}
