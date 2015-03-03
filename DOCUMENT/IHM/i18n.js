
/*global define*/
define(["text!dcpDocumentTemplate/api/v1/i18n/"], function(translation) {
    "use strict";
    translation = JSON.parse(translation);
    return {
        _catalog : translation.data.catalog,
        _locale : translation.data.locale,
        _ : function (key) {
            if (key && this._catalog[key]) {
                return this._catalog[key];
            }
            return key;
        },
        ___ : function (key, ctxt) {
            if (key && this._catalog._msgctxt_ && this._catalog._msgctxt_[ctxt] && this._catalog._msgctxt_[ctxt][key]) {
                return this._catalog._msgctxt_[ctxt][key];
            }
            return key;
        },
        getLocale : function() {
            return this._locale;
        }
    };
});