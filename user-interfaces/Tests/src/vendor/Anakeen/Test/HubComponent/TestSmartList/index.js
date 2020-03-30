import SmartListEntry from "./TestSmartListEntry";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-test-smart-list"]
) {
  // @ts-ignore
  window.ank.hub["ank-test-smart-list"].resolve(
    SmartListEntry,
    "ank-test-smart-list"
  );
}
