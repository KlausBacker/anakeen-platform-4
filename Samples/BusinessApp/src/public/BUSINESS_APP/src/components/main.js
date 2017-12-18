import Vue from 'vue';

import axios from 'axios';
const installCE = require('document-register-element/pony');

installCE(window, {
    type: 'auto',
    noBuiltIn: true,
});

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';
import GetTextPlugin from 'vue-gettext';
import translations from './translation.json';

Vue.use(GetTextPlugin, {
    availableLanguages: {
        en_US: 'English',
        fr_FR: 'Français',
    },
    defaultLanguage: 'fr_FR',
    languageVmMixin: {
        computed: {
            currentKebabCase: function adjustCulture() {
                return this.current.toLowerCase().replace('_', '-');
            },
        },
    },
    translations: translations,
    silent: true,
});

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
import ReportingTab from '@/reportingTab/reportingTab.vue';

// import Store from '@/store/store.vue';

// Vue.customElement('a4-store', Store);
Vue.customElement('ank-splitter', Splitter);
Vue.customElement('ank-welcome-tab', WelcomeTab);
Vue.customElement('ank-reporting-tab', ReportingTab);
Vue.customElement('ank-collections', Collections);

