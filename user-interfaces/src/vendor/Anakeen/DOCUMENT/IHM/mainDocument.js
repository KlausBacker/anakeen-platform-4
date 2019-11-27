/**
 * Main bootstraper
 */
import $ from "jquery";

import globalController from "./widgets/globalController/index";

globalController.then(() => {
  return import("@anakeen/user-interfaces/components/lib/AnkController.esm").then(AnakeenController => {
    AnakeenController.default.on("controllerReady", controller => {
      //Trigger an event when translation loaded

      let $document = $(".smart-element"),
        /* @var currentController SmartElementController */
        currentController;

      window.dcp = window.dcp || {};

      window.dcp.documentReady = false;

      if (window.dcp.viewData !== false && window.dcp.viewData.initid) {
        /* @var controller GlobalController */
        controller.addSmartElement($document, window.dcp.viewData, {
          router: true
        });
        currentController = controller.getScopedController($document);
        currentController.addEventListener("ready", (event, properties) => {
          window.document.title = properties.title;
          $("link[rel='shortcut icon']").attr("href", properties.icon);
        });
      } else {
        controller.addSmartElement($document);
        currentController = controller.getScopedController($document);
        currentController.addEventListener("ready", (event, properties) => {
          window.document.title = properties.title;
          $("link[rel='shortcut icon']").attr("href", properties.icon);
        });
      }

      window.dcp.document = $document;
    });

  });
});