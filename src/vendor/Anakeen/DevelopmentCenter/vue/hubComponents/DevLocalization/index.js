import Localization from "./DevLocalization.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-localization"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-localization"].resolve(
    Localization,
    "ank-dev-localization"
  );
}
