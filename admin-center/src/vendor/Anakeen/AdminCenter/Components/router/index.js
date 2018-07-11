import Vue from "vue";
import VueRouter from "vue-router";
import VueRouterMultiview from "vue-router-multi-view";

Vue.use(VueRouter);
Vue.use(VueRouterMultiview);

const routes = [];

export default new VueRouter({
  // mode: 'history',
  routes
});
