import Vue from 'vue';
import VueCustomElement from 'vue-custom-element';
import AdminParameters from './AdminCenterParameters';
import '@progress/kendo-ui/js/kendo.treelist';
import '@progress/kendo-ui/js/kendo.window';
import '@progress/kendo-ui/js/kendo.button';
import AnkAxios from "../Components/utils/axios";

Vue.use(VueCustomElement);
Vue.use(AnkAxios);
Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.kendo = Vue.prototype.$kendo = kendo;
Vue.customElement('ank-admin-parameters', AdminParameters);
