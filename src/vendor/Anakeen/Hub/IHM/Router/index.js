import Vue from "vue";
import VueRouter from "vue-router";
import VueRouterMultiView from "./RouterMultiView";
import HubStation from "../Components/Hub/Hub.vue";

Vue.use(VueRouter);
Vue.use(VueRouterMultiView, { forceMultiViews: true });

export const routes = [
  {
    path: "/",
    redirect: {
      path: "/hub/station/"
    }
  },
  {
    path: "/hub/station/",
    name: "HubStation",
    component: {
      template: "<div></div>"
    },
    meta: {
      label: "Hub Station"
    }
  }
];

const Router = new VueRouter({
  routes,
  mode: "history",
  saveScrollPosition: true
});

export default Router;
