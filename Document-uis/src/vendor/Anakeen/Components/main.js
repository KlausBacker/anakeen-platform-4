import Vue from 'vue';
import axios from 'axios';

import 'document-register-element/build/document-register-element';

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';

Vue.use(VueCustomElement);
Vue.prototype.$http = axios.create({
  baseURL: '/api/v1'
});
Vue.prototype.$kendo = kendo;

// import and register your component(s)
import Authent from './Authent/Authent.vue';
Vue.customElement('a4-authent', Authent);