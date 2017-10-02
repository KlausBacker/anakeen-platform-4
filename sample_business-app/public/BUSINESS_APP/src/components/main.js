import Vue from 'vue';
import 'document-register-element/build/document-register-element';

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';

Vue.use(VueCustomElement);

// import and register your component(s)
import Base from './base/base.vue';
Vue.customElement('a4-base', Base);