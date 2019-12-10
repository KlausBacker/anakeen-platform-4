import $ from "jquery";
import translatorFactory from "./translatorFactory";

export default function getTranslator(applicationName, success, error) {
  window.dcp = window.dcp || {};
  window.dcp.knownTranslations = window.dcp.knownTranslations || {};
  var translatePromise = new Promise(function promiseInternalFunction(resolve, reject) {
    if (window.dcp.knownTranslations[applicationName]) {
      resolve(window.dcp.knownTranslations[applicationName]);
    }

    $.ajax({
      url: "/api/v2/i18n/" + window.encodeURIComponent(applicationName),
      type: "GET",
      dataType: "text"
    })
      .done(function getI18nDone(data) {
        window.dcp.knownTranslations[applicationName] = translatorFactory(data);
        resolve(window.dcp.knownTranslations[applicationName]);
      })
      .fail(function getI18nFail(error) {
        reject(error);
      });
  });

  translatePromise.then(
    function translatorThenOK(translator) {
      if ($.isFunction(success)) {
        success(translator);
      }
    },
    function translatorThenKO(data) {
      if ($.isFunction(error)) {
        error(data);
      }
    }
  );

  return translatePromise;
}
