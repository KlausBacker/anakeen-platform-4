/**
 * Main bootstraper
 */
import $ from "jquery";

import globalController from "./widgets/globalController/index";
import "./mainDocument.css";

globalController.then(() => {
  return import(
    "@anakeen/user-interfaces/components/lib/AnkController.esm" /* webpackChunkName: "mainController" */
  ).then(AnakeenController => {
    AnakeenController.default.on("controllerReady", controller => {
      //Trigger an event when translation loaded
      let $document = $(".smart-element"),
        /* @var currentController SmartElementController */
        currentController;

      window.dcp = window.dcp || {};

      window.dcp.documentReady = false;

      if (window.dcp.viewData !== false && window.dcp.viewData.initid) {
        /* @var controller GlobalController */
        const controllerUidTmp = controller.addSmartElement($document, window.dcp.viewData, {
          router: true,
          autoInitialize: false
        });
        const promise = Promise.resolve(controllerUidTmp);
        promise.then(value => {
          currentController = controller.getScopedController(value);
          currentController.initializeSmartElement().then(() => {
            currentController.addEventListener("ready", { persistent: true }, (event, properties) => {
              window.document.title = properties.title;
              $("link[rel='shortcut icon']").attr("href", properties.icon);
            });
          });
        });
      } else {
        const controllerUidTmp = controller.addSmartElement($document, null, { autoInitialize: false });
        const promise = Promise.resolve(controllerUidTmp);
        return promise.then(value => {
          currentController = controller.getScopedController(value);
          currentController.initializeSmartElement().then(() => {
            currentController.addEventListener("ready", { persistent: true }, (event, properties) => {
              window.document.title = properties.title;
              $("link[rel='shortcut icon']").attr("href", properties.icon);
            });
          });
        });
      }

      window.dcp.document = $document;
    });
  });
});
