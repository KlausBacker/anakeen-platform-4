import FullSearchManager from "./DevelCenterFullSearchManagerEntry";

if (window && window.ank && window.ank.hub && window.ank.hub["ank-hub-fullsearch-manager"]) {
  // @ts-ignore
  window.ank.hub["ank-hub-fullsearch-manager"].resolve(FullSearchManager, "ank-hub-fullsearch-manager");
}
