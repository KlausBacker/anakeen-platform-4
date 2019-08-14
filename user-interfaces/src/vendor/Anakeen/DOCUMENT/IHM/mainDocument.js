/**
 * Main bootstraper
 */
import $ from "jquery";
import "../../../../../webpackConfig/kendo/kendo";

$.get("/api/v2/i18n/DOCUMENT").done(function translationLoaded(catalog) {
  //Trigger an event when translation loaded
  window.dcp.i18n = catalog;

  var _ = require("underscore");
  require("dcpDocument/widgets/globalController");

  let $document = $(".document"),
    currentValues,
    varWidgetValue = "widgetValue",
    /* @var currentController SmartElementController */
    currentController;

  window.dcp = window.dcp || {};

  window.dcp.documentReady = false;

  if (!window.dcp.viewData && window.location.hash) {
    currentValues = window.location.hash;
    if (currentValues[0] === "#") {
      currentValues = currentValues.slice(1);
    }
    if (currentValues.indexOf(varWidgetValue) === 0) {
      try {
        window.dcp.viewData = JSON.parse(currentValues.slice(varWidgetValue.length));
      } catch (ex1) {
        try {
          window.dcp.viewData = JSON.parse(decodeURI(currentValues.slice(varWidgetValue.length)));
        } catch (ex2) {
          $document.documentController("showMessage", {
            type: "error",
            message: "unable to retrieve document"
          });
        }
      }
    }
  }

  if (window.dcp.viewData !== false && window.dcp.viewData.initid) {
    window.ank.smartElement.globalController.on("controllerReady", controller => {
      /* @var controller GlobalController */
      controller.addSmartElement($document, window.dcp.viewData, {
        router: true
      });
      currentController = controller.scope($document);
      currentController.addEventListener("ready", (event, properties) => {
        window.document.title = properties.title;
        $("link[rel='shortcut icon']").attr("href", properties.icon);
      });
    });
    $document.one("documentready", function launchReady() {
      _.each(window.dcp.messages, function(msg) {
        currentController.showMessage({
          type: msg.type,
          message: msg.contentText
        });
      });
    });
  } else {
    window.ank.smartElement.globalController.on("controllerReady", controller => {
      controller.addSmartElement(".document");
      currentController = controller.scope($document);
      currentController.addEventListener("ready", (event, properties) => {
        window.document.title = properties.title;
        $("link[rel='shortcut icon']").attr("href", properties.icon);
      });
    });
  }

  window.dcp.document = $document;
});
