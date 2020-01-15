import kendo from "@progress/kendo-ui/js/kendo.core";
import axios from "axios";
import Vue from "vue";
import Login from "./LoginPage.vue";
import I18nMixin from "../../../../../../components/mixins/AnkVueComponentMixin/I18nMixin";

import VueI18n from "vue-i18n";

Vue.http = Vue.prototype.$http = axios;
Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.kendo = Vue.Kendo = Vue.prototype.$kendo = kendo;

const i18nLoginPage = new VueI18n({
  locale: "fr-FR",
  messages: {
    "fr-FR": { "authent.Connect to {s}": "Connexion à {s}" },
    "en-US": { "authent.Connect to {s}": "Connection to {s}" }
  }
});

new Vue({
  i18n: i18nLoginPage,
  el: "#login",
  mixins: [I18nMixin],
  components: {
    Login: function(resolve) {
      // Download locale catalog before display components
      Vue.$_globalI18n
        .recordCatalog(i18nLoginPage)
        .then(() => {
          resolve(Login);
        })
        .catch(() => {
          resolve(Login);
        });
    }
  },
  data: {
    nsSde: ""
  },
  created() {
    this.nsSde = window.nsSde;
    window.document.title = this.$t("authent.Connect to {s}", { s: this.nsSde });
    this.$on("localeChanged", newLocale => {
      window.document.title = this.$t("authent.Connect to {s}", { s: this.nsSde });
      window.document.querySelector("html").setAttribute("lang", newLocale.substr(0, 2));
    });
  },
  template: "<login :nsSde='nsSde'></login>"
});
