import SmartCriteriaEntry from "./TestSmartCriteriaEntry";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-test-smart-criteria"]
) {
  // @ts-ignore
  window.ank.hub["ank-test-smart-criteria"].resolve(
    SmartCriteriaEntry,
    "ank-test-smart-criteria"
  );
}
