import SmartCriteriaEntry from "./TestFulltextSmartCriteriaEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-test-fulltext-smart-criteria"]
) {
  // @ts-ignore
  window.ank.hub["ank-test-fulltext-smart-criteria"].resolve(SmartCriteriaEntry, "ank-test-fulltext-smart-criteria");
}
