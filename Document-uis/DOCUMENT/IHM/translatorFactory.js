window.dcp = window.dcp || {};

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define(["jquery", "dcpDocument/i18n"], factory);
    } else {
        factory(window.$, window.dcp._i18n);
    }
}(window, function factory_translatorFactory($, i18n)
{
    "use strict";

    var knownTranslation = {};

    window.dcp.translatorFactory = function translatorFactory(applicationName, success, error)
    {
        var translatePromise = new Promise(function promiseInternalFunction(resolve, reject)
        {
            if (knownTranslation[applicationName]) {
                resolve(knownTranslation[applicationName]);
            }

            $.ajax({
                "url": "api/v1/i18n/" + window.encodeURIComponent(applicationName),
                "type": "GET",
                "dataType": "text"
            }).done(function getI18nDone(data)
            {
                knownTranslation[applicationName] = i18n(data);
                resolve(knownTranslation[applicationName]);
            }).fail(function getI18nFail(error)
            {
                reject(error)
            });
        });

        translatePromise.then(function translatorThenOK(translator)
        {
            if ($.isFunction(success)) {
                success(translator);
            }
        }, function translatorThenKO(data)
        {
            if ($.isFunction(error)) {
                error(data);
            }
        });

        return translatePromise;
    };

    return window.dcp.translatorFactory;
}));
