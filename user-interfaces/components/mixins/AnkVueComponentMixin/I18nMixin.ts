/* tslint:disable:variable-name */
import VueI18n from "vue-i18n";
import { Component, Vue } from "vue-property-decorator";
import I18nSetup from "./I18nSetup";
// A mixin to share traductions between components
Vue.use(VueI18n);
Vue.use(I18nSetup);

const i18n = new VueI18n({
  locale: "fr-FR",
  messages: {}
});

@Component({
  i18n
})
export default class AnkVueI18nMixin extends Vue {
  public _i18n: VueI18n;

  public beforeCreate() {
    if (this.$root.$i18n && !this.$_globalI18n.sharedVueI18n) {
      // Use root catalog as shared catalog
      this.$_globalI18n.sharedVueI18n = this.$root.$i18n;
    }

    if (this.$_globalI18n.sharedVueI18n) {
      this._i18n = this.$_globalI18n.sharedVueI18n;
    } else {
      this.$_globalI18n.sharedVueI18n = this._i18n;
      this.$_globalI18n.recordCatalog();
    }
    this.$_globalI18n.i18nBus.$on("localeLoaded", () => {
      // REEMIT localeLoaded to local components
      this.$emit("localeLoaded");
    });

    this.$_globalI18n.i18nBus.$on("localeChanged", lang => {
      this.$i18n.locale = lang;
      // REEMIT localeChanged to local components
      this.$emit("localeChanged", lang);
    });
    this.$i18n.locale = this.$_globalI18n.locale;
  }

  public mounted() {
    if (this.$_globalI18n.loaded) {
      // Shared catalog already loaded
      this.$emit("localeLoaded");
    }
  }
}
