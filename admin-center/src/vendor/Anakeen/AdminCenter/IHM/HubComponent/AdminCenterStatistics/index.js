import Statistics from "./AdminCenterStatisticsEntry.vue";

if (window && window.ank && window.ank.hub && window.ank.hub["ank-admin-statistics"]) {
  window.ank.hub["ank-admin-statistics"].resolve(Statistics, "ank-admin-statistics");
}
