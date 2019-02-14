import { Route, VueRouter } from "vue-router/types/router";
declare module "vue/types/vue" {
  // tslint:disable-next-line:interface-name
  interface Vue {
    $router: VueRouter;
    $route: Route;
  }
}
