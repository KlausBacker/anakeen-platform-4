import Vue from "vue";
import VueCustomElement from "vue-custom-element";

import RoutePlugin from "./RoutePlugin";

import "@progress/kendo-ui/js/kendo.mobile.switch";
import "@progress/kendo-ui/js/kendo.tabstrip";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import VueAdminSetup from "../Vue/vueCommonConfig.js";

Vue.use(VueCustomElement);
Vue.use(VueAdminSetup);

Vue.customElement("ank-admin-routes", RoutePlugin);
