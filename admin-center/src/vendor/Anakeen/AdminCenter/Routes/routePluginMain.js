import Vue from 'vue';
import VueCustomElement from 'vue-custom-element';

import RoutePlugin from './RoutePlugin.vue';

Vue.use(VueCustomElement);

Vue.customElement('ank-admin-routes', RoutePlugin);