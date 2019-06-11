import TeManager from "./AdminCenterTeManagerEntry";

if (
  window &&
  window.ank &&
  window.ank.hub &&
  window.ank.hub["ank-hub-te-manager"]
) {
  // @ts-ignore
  window.ank.hub["ank-hub-te-manager"].resolve(TeManager, "ank-hub-te-manager");
}
