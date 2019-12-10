export default function catalog(translation) {
  var isString = function isString(obj) {
    return typeof obj === "string";
  };
  if (isString(translation)) {
    try {
      translation = JSON.parse(translation);
    } catch (e) {
      translation = { data: { catalog: {} } };
      console.error("Locale catalog error : " + e.message);
    }
  }
  return {
    _catalog: translation.data.catalog,
    _locale: translation.data.locale,

    /**
     * Return key translation
     * @param key text to translate
     * @returns string
     */
    _: function i18n_gettext(key) {
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
    ___: function i18n_pgettext(key, ctxt) {
      if (
        key &&
        this._catalog &&
        this._catalog._msgctxt_ &&
        this._catalog._msgctxt_[ctxt] &&
        this._catalog._msgctxt_[ctxt][key]
      ) {
        return this._catalog._msgctxt_[ctxt][key];
      }
      return key;
    },
    /**
     * Return some info on the current locale
     *
     * @returns {locale|{culture}|*|string|string}
     */
    getLocale: function i18n_getLocale() {
      return this._locale;
    }
  };
}
