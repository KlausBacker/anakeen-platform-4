import Vue from 'vue';
import axios from 'axios';

const installCE = require('document-register-element/pony');

installCE(window, {
    type: 'auto',
    noBuiltIn: true
});

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
Vue.prototype.$kendo = Vue.kendo = kendo;

// import and register your component(s)
import Authent from './Authent/Authent.vue';
import Document from './Document/Document.vue';
import DocumentList from './DocumentList/documentList.vue';
import DocumentTabs from './DocumentTabs/documentTabs.vue';
import AnakeenLoading from './AnakeenLoading/AnakeenLoading.vue';

Vue.customElement('ank-loading', AnakeenLoading);
Vue.customElement('ank-authent', Authent);
Vue.customElement('ank-document', Document);
Vue.customElement('ank-document-list', DocumentList);
Vue.customElement('ank-document-tabs', DocumentTabs);
