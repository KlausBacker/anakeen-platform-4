import Security from "./DevSecurity.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-security"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-security"].resolve(Security, "ank-dev-security");
}
