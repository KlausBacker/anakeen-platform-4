import { AxiosInstance } from "axios";
import Navigo from "navigo";
import { Store } from "vuex";
import VueEventBus from "./utils/VueEventBus";

interface IVueAxiosInstance extends AxiosInstance {
  errorEvents: any;
}

declare module "vue/types/vue" {
  // tslint:disable-next-line:interface-name
  interface Vue {
    $ankHubRouter: { internal: Navigo; external: Navigo };
    $http: IVueAxiosInstance;
    $store: Store<any>;
    $_hubEventBus: VueEventBus;
  }
}
