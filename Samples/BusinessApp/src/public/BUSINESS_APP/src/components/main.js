import Vue from 'vue';

import axios from 'axios';
import 'document-register-element/build/document-register-element';

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';

Vue.use(VueCustomElement);
Vue.http = Vue.prototype.$http = axios.create({
  baseURL: '/api/v1',
  timeout: 1000
});
Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.kendo = Vue.prototype.$kendo = kendo;
// import and register your component(s)
// import Base from './base/base.vue';
import Splitter from './splitter/splitter.vue';
import Collections from './collectionsList/collections.vue';
import Documents from './documentsList/documents.vue';
import OpenDocument from './openDocument/openDocument.vue';
import Store from './store/store.vue';

// Vue.customElement('a4-base', Base);
Vue.customElement('a4-store', Store);
Vue.customElement('a4-splitter', Splitter);
Vue.customElement('a4-collections', Collections);
Vue.customElement('a4-documents', Documents);
Vue.customElement('a4-open-document', OpenDocument);