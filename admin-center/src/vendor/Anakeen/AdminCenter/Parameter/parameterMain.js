import Vue from 'vue';
import VueCustomElement from 'vue-custom-element';
import AdminParameter from './AdminCenterParameter';

Vue.use(VueCustomElement);


Vue.customElement('ank-admin-parameter', AdminParameter);