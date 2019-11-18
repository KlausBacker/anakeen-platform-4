import "@progress/kendo-ui/js/cultures/kendo.culture.fr-FR";
import "@progress/kendo-ui/js/kendo.core";
import VueI18n from "vue-i18n";
import I18nSetup from "../mixins/AnkVueComponentMixin/I18nSetup.js";
import axios from "../utils/axios";

const setLocale = (Vue, kendo, locale?) => {
  if (locale) {
    const kendoLocale = locale.replace("_", "-");
    kendo.culture(kendoLocale);
    Vue.$_globalI18n.setLocale(locale);
  } else {
    if (Vue.$_globalI18n.locale) {
      locale = Vue.$_globalI18n.locale;
      const kendoLocale = locale.replace("_", "-");
      kendo.culture(kendoLocale);
    } else {
      kendo.culture("fr-FR");
    }
  }
};

export default function install(Vue) {
  if (Vue.__ank_components_setup__ === true) {
    return;
  }
  Vue.__ank_components_setup__ = true;

  Vue.use(VueI18n);
  Vue.use(I18nSetup);

  Vue.http = Vue.prototype.$http = axios;
  Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
  Vue.kendo = Vue.Kendo = Vue.prototype.$kendo = kendo;

  // Fetch locale for component (Vue/kendo) on server or set fr-FR by default
  Vue.http
    .get("/api/v2/ui/users/current")
    .then(response => {
      if (!(response && response.data && response.data.data && response.data.data.locale)) {
        throw new Error("[src setup] Invalid locale server response");
      }
      const locale = response.data.data.locale;
      const parsedLocale = locale.split(".")[0].replace("_", "-");
      setLocale(Vue, kendo, parsedLocale);
    })
    .catch(() => {
      setLocale(Vue, kendo);
    });
}
