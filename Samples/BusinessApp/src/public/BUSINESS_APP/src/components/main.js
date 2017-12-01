import Vue from 'vue';

import axios from 'axios';
const installCE = require('document-register-element/pony');

installCE(window, {
    type: 'auto',
    noBuiltIn: true
});

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';

Vue.use(VueCustomElement);
Vue.http = Vue.prototype.$http = axios.create({
    baseURL: '/api/v1',
    timeout: 10000,
});

Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.kendo = Vue.prototype.$kendo = kendo;

// import and register your component(s)
import Splitter from '@/splitter/splitter.vue';
import WelcomeTab from '@/welcomeTab/welcomeTab.vue';
import Collections from '@/collectionsList/collections.vue';
import Button from '@/createButton/createButton.vue';
import Store from '@/store/store.vue';

Vue.customElement('a4-store', Store);
Vue.customElement('a4-splitter', Splitter);
Vue.customElement('a4-welcome-tab', WelcomeTab);
Vue.customElement('a4-collections', Collections);

