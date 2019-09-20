import { I18nBus } from "./I18nBus";
import VueI18n from "vue-i18n";

import Axios from "axios";

export function initComponentLocale($i18n) {
  return new Promise((resolve, reject) => {
    Axios.get("/locale/catalog.json")
      .then(response => {
        Object.keys(response.data).forEach(key => {
          $i18n.mergeLocaleMessage(key, response.data[key]);
        });
        resolve();
      })
      .catch(() => {
        reject();
      });
  });
}

export default function install(Vue) {
  if (!Vue.prototype.$_globalI18n) {
    Vue.use(VueI18n);
    const i18nDefault = new VueI18n({
      locale: "fr-FR",
      messages: {}
    });

    Vue.$_globalI18n = Vue.prototype.$_globalI18n = {
      loaded: false,
      loading: false,
      i18nBus: I18nBus,
      sharedVueI18n: null,
      locale: "fr-FR",
      recordCatalog: function(i18n) {
        if (i18n) this.sharedVueI18n = i18n;
        if (!this.sharedVueI18n) {
          this.sharedVueI18n = i18nDefault;
        }
        if (this.loaded === true) {
          return new Promise(resolve => {
            this.i18nBus.$emit("localeLoaded");
            resolve();
          });
        } else if (this.loading === true) {
          return new Promise(resolve => {
            this.i18nBus.$on("localeLoaded", () => {
              resolve();
            });
          });
        } else {
          this.loading = true;
          return initComponentLocale(this.sharedVueI18n).then(() => {
            this.loaded = true;
            this.i18nBus.$emit("localeLoaded");
          });
        }
      },
      setLocale: function(lang) {
        this.locale = lang;
        if (this.sharedVueI18n) {
          this.sharedVueI18n.locale = lang;
        }
        this.i18nBus.$emit("localeChanged", lang);
      }
    };
  }
}
