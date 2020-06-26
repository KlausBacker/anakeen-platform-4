import Vue from "vue";
import { AxiosInstance, AxiosStatic } from "axios";
import "vue-gettext/types/vue";
import "vue-i18n/types/";
import VueI18n from "vue-i18n/types/";

declare module "vue/types/vue" {
  interface Vue {
    _uid: number;
    $http: AxiosInstance;
    $_globalI18n: {
      locale: string;
      loaded: boolean;
      i18nBus: Vue;
      sharedVueI18n: VueI18n;
      recordCatalog(vi18n?: VueI18n): Promise<void>;
      setLocale(lang: string): void;
    };
    $axios: AxiosStatic;
    $t: typeof VueI18n.prototype.t;
    $tc: typeof VueI18n.prototype.tc;
    $te: typeof VueI18n.prototype.te;
    $d: typeof VueI18n.prototype.d;
    $n: typeof VueI18n.prototype.n;
  }
}

declare module "vue-i18n/types/" {
  interface Vue {
    _uid: number;
    $http: AxiosInstance;
    $axios: AxiosStatic;
  }
}
