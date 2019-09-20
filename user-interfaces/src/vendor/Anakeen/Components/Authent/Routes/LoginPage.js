import Vue from "vue";
import Login from "./LoginPage.vue";
import LoadScript from "vue-m-loader";
import I18nMixin from "../../../../../../components/mixins/AnkVueComponentMixin/I18nMixin";

import VueI18n from "vue-i18n";

Vue.use(LoadScript);

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
      Vue.$_globalI18n.recordCatalog(i18nLoginPage).finally(() => {
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
