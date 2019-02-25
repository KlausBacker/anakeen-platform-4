import { AxiosInstance } from "axios";
import { Route, VueRouter } from "vue-router/types/router";

interface IVueAxiosInstance extends AxiosInstance {
  errorEvents: any;
}

declare module "vue/types/vue" {
  // tslint:disable-next-line:interface-name
  interface Vue {
    $router: VueRouter;
    $route: Route;
    $http: IVueAxiosInstance;
  }
}
