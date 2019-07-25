import UserInterface from "./DevUserInterface.vue";

if (
  window &&
  // @ts-ignore
  window.ank &&
  // @ts-ignore
  window.ank.hub &&
  // @ts-ignore
  window.ank.hub["ank-dev-user-interface"]
) {
  // @ts-ignore
  window.ank.hub["ank-dev-user-interface"].resolve(UserInterface, "ank-dev-user-interface");
}
