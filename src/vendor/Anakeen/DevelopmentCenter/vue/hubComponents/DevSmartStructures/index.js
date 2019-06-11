import SmartStructures from "./DevSmartStructures.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-structures"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-structures"].resolve(
    SmartStructures,
    "ank-dev-structures"
  );
}
