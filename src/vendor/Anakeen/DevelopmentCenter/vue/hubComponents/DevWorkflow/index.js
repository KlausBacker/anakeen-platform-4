import SmartStructures from "./DevWorkflow.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-workflow"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-workflow"].resolve(
    SmartStructures,
    "ank-dev-workflow"
  );
}
