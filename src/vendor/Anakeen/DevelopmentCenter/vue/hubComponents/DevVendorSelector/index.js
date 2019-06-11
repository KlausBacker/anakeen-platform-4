import VendorSelector from "devComponents/VendorSelector/VendorSelector.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-vendor-selector"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-vendor-selector"].resolve(
    VendorSelector,
    "ank-dev-vendor-selector"
  );
}
