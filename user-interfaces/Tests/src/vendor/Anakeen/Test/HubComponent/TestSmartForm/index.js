import SmartFormEntry from "./TestSmartFormEntry";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-test-smart-form"]
) {
  // @ts-ignore
  window.ank.hub["ank-test-smart-form"].resolve(
    SmartFormEntry,
    "ank-test-smart-form"
  );
}
