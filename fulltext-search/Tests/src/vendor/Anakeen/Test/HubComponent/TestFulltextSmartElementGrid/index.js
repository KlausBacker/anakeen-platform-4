import FulltextSmartGrid from "./TestFulltextSmartGridEntry.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-test-fulltext-smart-grid"]
) {
  // @ts-ignore
  window.ank.hub["ank-test-fulltext-smart-grid"].resolve(FulltextSmartGrid, "ank-test-fulltext-smart-grid");
}
