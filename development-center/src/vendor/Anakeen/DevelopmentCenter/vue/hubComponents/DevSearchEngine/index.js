import Search from "./DevSearchEngine.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-search"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-search"].resolve(Search, "ank-dev-search");
}
