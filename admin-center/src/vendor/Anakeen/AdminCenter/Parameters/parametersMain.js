import Vue from "vue";
import VueCustomElement from "vue-custom-element";
import AdminParameters from "./AdminCenterParameters";
import "@progress/kendo-ui/js/kendo.treelist";
import "@progress/kendo-ui/js/kendo.window";
import "@progress/kendo-ui/js/kendo.button";

import VueAdminSetup from "../Vue/vueCommonConfig.js";

Vue.use(VueCustomElement);
Vue.use(VueAdminSetup);

Vue.customElement("ank-admin-parameters", AdminParameters);
