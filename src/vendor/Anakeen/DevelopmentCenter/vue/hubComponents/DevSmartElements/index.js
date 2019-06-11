import Elements from "./DevSmartElements.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-elements"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-elements"].resolve(Elements, "ank-dev-elements");
}
