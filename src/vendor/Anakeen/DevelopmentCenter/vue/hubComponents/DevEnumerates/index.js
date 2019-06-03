import SmartStructures from "./DevEnumerates.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-enumerates"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-enumerates"].resolve(
    SmartStructures,
    "ank-dev-enumerates"
  );
}
