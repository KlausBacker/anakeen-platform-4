import { AxiosInstance } from "axios";
import Navigo from 'navigo';
import { Store } from "vuex";

interface IVueAxiosInstance extends AxiosInstance {
  errorEvents: any;
}

declare module "vue/types/vue" {
  // tslint:disable-next-line:interface-name
  interface Vue {
    $ankHubRouter: Navigo;
    $http: IVueAxiosInstance;
    $store: Store<any>;
  }
}
