/*global define*/
// use "text!dcpContextRoot/api/v1/i18n/DOCUMENT" to get only DDUI translation

window.dcp = window.dcp || {};

(function umdRequire(root, factory)
{
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([], factory);
    }
    root.dcp._i18n = factory([]);
}(window, function i18n()
{
    "use strict";
    return function catalog(translation)
    {
        try {
            translation = JSON.parse(translation);
        } catch (e) {
            translation = {data: {catalog: {}}};
            console.error("Locale catalog error : " + e.message);
        }
        return {
            _catalog: translation.data.catalog,
            _locale: translation.data.locale,

            /**
             * Return key translation
             * @param key text to translate
             * @returns string
             */
            _: function i18n_gettext(key)
            {
                if (key && this._catalog[key]) {
                    return this._catalog[key];
                }
                return key;
            },
            /**
             * Return key translation in context
             * @param key text to translate
             * @param ctxt context
             * @returns {*}
             */
            ___: function i18n_pgettext(key, ctxt)
            {
                if (key && this._catalog && this._catalog._msgctxt_ && this._catalog._msgctxt_[ctxt] && this._catalog._msgctxt_[ctxt][key]) {
                    return this._catalog._msgctxt_[ctxt][key];
                }
                return key;
            },
            getLocale: function i18n_getLocale()
            {
                return this._locale;
            }
        };
    };
}));