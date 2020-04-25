import { AxiosInstance } from "axios";
import VueI18n from "vue-i18n";

declare module "vue/types/vue" {
  interface Vue {
    $http: AxiosInstance;
    $t: typeof VueI18n.prototype.t;
    $tc: typeof VueI18n.prototype.tc;
    $te: typeof VueI18n.prototype.te;
    $d: typeof VueI18n.prototype.d;
    $n: typeof VueI18n.prototype.n;
  }
}
