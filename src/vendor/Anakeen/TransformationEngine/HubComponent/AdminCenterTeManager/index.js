import TeManager from "./AdminCenterTeManagerEntry";

if (window && window.ank && window.ank.hub && window.ank.hub.AdminTeManager) {
  // @ts-ignore
  window.ank.hub.AdminTeManager.resolve(TeManager, "ank-hub-te-manager");
}
