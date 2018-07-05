import Vue from 'vue';
import VueCustomElement from 'vue-custom-element';

import RoutePlugin from './RoutePlugin';

import '@progress/kendo-ui/js/kendo.mobile.switch';
import '@progress/kendo-ui/js/kendo.treelist';
import '@progress/kendo-ui/js/kendo.window';

Vue.use(VueCustomElement);

Vue.customElement('ank-admin-routes', RoutePlugin);