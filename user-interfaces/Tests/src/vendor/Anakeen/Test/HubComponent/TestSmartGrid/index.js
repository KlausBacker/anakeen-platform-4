import SmartGridEntry from "./TestSmartGridEntry";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-test-smart-grid"]
) {
  // @ts-ignore
  window.ank.hub["ank-test-smart-grid"].resolve(
    SmartGridEntry,
    "ank-test-smart-grid"
  );
}
