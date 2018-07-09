window.dcp = window.dcp || {};

(function umdRequire(root, requireFunction) {
  "use strict";

  if (typeof define === "function" && define.amd) {
    define(["jquery", "dcpDocument/i18n/translatorFactory"], requireFunction);
  } else {
    requireFunction(window.$, window.dcp.translatorFactory);
  }
})(window, function require_getTranslator($, translatorFactory) {
  "use strict";

  var knownTranslations = {};

  window.dcp.getTranslator = function getTranslator(
    applicationName,
    success,
    error
  ) {
    var translatePromise = new Promise(function promiseInternalFunction(
      resolve,
      reject
    ) {
      if (knownTranslations[applicationName]) {
        resolve(knownTranslations[applicationName]);
      }

      $.ajax({
        url: "api/v2/i18n/" + window.encodeURIComponent(applicationName),
        type: "GET",
        dataType: "text"
      })
        .done(function getI18nDone(data) {
          knownTranslations[applicationName] = translatorFactory(data);
          resolve(knownTranslations[applicationName]);
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
  };

  return window.dcp.getTranslator;
});
