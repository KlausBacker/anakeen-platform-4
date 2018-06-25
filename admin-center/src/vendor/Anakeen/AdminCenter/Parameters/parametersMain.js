import Vue from 'vue';
import VueCustomElement from 'vue-custom-element';
import AdminParameters from './AdminCenterParameters';
import '@progress/kendo-ui/js/kendo.treelist';

Vue.use(VueCustomElement);

Vue.customElement('ank-admin-parameters', AdminParameters);
