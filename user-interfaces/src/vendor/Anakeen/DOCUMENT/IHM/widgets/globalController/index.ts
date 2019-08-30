// @ts-ignore
import { loadCatalog, storeCatalog } from "dcpDocument/i18n/catalogStorage";
// @ts-ignore
import AnakeenController from "dcpDocument/widgets/globalController/GlobalController";

import "../../../../../../../webpackConfig/kendo/kendo";
window.ank = window.ank || {};
window.ank.smartElement = window.ank.smartElement || {};
if (!window.ank.smartElement.globalController) {
  window.ank.smartElement.globalController = new AnakeenController(false);
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
