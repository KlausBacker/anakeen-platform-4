import $ from "jquery";
import { loadCatalog, storeCatalog } from "../../i18n/catalogStorage";
import AnakeenController from "./GlobalController";

import "../../../../../../../webpackConfig/kendo/kendo";

window.ank = window.ank || {};
window.ank.smartElement = window.ank.smartElement || {};

export default new Promise((resolve, reject) => {
  try {
    if (!window.ank.smartElement.globalController) {
      window.ank.smartElement.globalController = new AnakeenController(false);
    }

    const catalog = loadCatalog();
    if (!catalog) {
      $.ajax("/api/v2/i18n/DOCUMENT")
        .done(response => {
          storeCatalog(response);
          resolve(window.ank.smartElement.globalController.init());
        })
        .fail(error => {
          reject(error);
        });
    } else {
      resolve(window.ank.smartElement.globalController.init());
    }
  } catch (e) {
    reject(e);
  }
});
