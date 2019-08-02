import axios from "../utils/axios";
import GetTextPlugin from "vue-gettext/src/index.js";
import "@progress/kendo-ui/js/kendo.button";
import "@progress/kendo-ui/js/kendo.tabstrip";
import "@progress/kendo-ui/js/kendo.dropdownlist";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.popup";
import "@progress/kendo-ui/js/kendo.pager";
import "@progress/kendo-ui/js/kendo.sortable";
import "@progress/kendo-ui/js/kendo.listview";
import "@progress/kendo-ui/js/kendo.tabstrip";
import "@progress/kendo-ui/js/cultures/kendo.culture.fr-FR";
import translations from "./translation.json";

const setLocale = (Vue, kendo, locale) => {
  if (locale) {
    const kendoLocale = locale.replace("_", "-");
    kendo.culture(kendoLocale);
    Vue.config.language = locale;
  } else {
    if (Vue.config.language) {
      locale = Vue.config.language;
      const kendoLocale = locale.replace("_", "-");
      kendo.culture(kendoLocale);
    } else {
      kendo.culture("fr-FR");
    }
  }
};

export default function install(Vue, opts = { globalVueComponent: false, webComponents: false }) {
  if (Vue.__ank_components_setup__ === true) return;
  Vue.__ank_components_setup__ = true;
  // jscs:ignore disallowFunctionDeclarations
  Vue.use(GetTextPlugin, {
    availableLanguages: {
      en_US: "English",
      fr_FR: "Français"
    },
    defaultLanguage: "fr_FR",
    languageVmMixin: {
      computed: {
        currentKebabCase: function adjustCulture() {
          return this.current.toLowerCase().replace("_", "-");
        }
      }
    },
    translations: translations,
    silent: true
  });

  Vue.http = Vue.prototype.$http = axios;

  Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
  Vue.kendo = Vue.Kendo = Vue.prototype.$kendo = kendo;
  Vue.prototype.$dockEventBus = new Vue();

  // Fetch locale for component (Vue/kendo) on server or set fr_FR by default
  Vue.http
    .get("/api/v2/ui/users/current")
    .then(response => {
      if (!(response && response.data && response.data.data && response.data.data.locale)) {
        throw "[src setup] Invalid locale server response";
      }
      const locale = response.data.data.locale;
      const parsedLocale = locale.split(".")[0];
      setLocale(Vue, kendo, parsedLocale);
    })
    .catch(() => {
      setLocale(Vue, kendo);
    });

  if (opts.webComponents) {
    const VueCustomElement = require("vue-custom-element").default;
    Vue.use(VueCustomElement);
    const installCE = require("document-register-element/pony");

    installCE(window, {
      type: "auto",
      noBuiltIn: true
    });
  }
}
