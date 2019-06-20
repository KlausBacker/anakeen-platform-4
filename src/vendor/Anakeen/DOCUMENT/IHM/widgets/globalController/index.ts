import GlobalController from "./GlobalController";
import { storeCatalog, loadCatalog } from "../../i18n/catalogStorage";

declare global {
  interface Window {
    ank?: {
      smartElement?: {
        globalController?: GlobalController;
        globalController2?: GlobalController;
      },
      i18n?: any
    };
  }
}

window.ank = window.ank || {};
window.ank.smartElement = window.ank.smartElement || {};
if (!window.ank.smartElement.globalController) {
  window.ank.smartElement.globalController = new GlobalController(false);
}

const catalog = loadCatalog();
if (!catalog) {
  fetch("/api/v2/i18n/DOCUMENT")
    .then(response => {
      return response.json();
    })
    .then(response => {
      storeCatalog(response);
      window.ank.smartElement.globalController.init();
    });
} else {
  window.ank.smartElement.globalController.init();
}
