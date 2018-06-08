import Vue from 'vue';
import axios from 'axios';

const installCE = require('document-register-element/pony');

installCE(window, {
    type: 'auto',
    noBuiltIn: true,
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
    baseURL: '/',
    timeout: 10000,
});

Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.prototype.$kendo = Vue.kendo = kendo;

// Fetch user language from server
Vue.http.get('/api/v2/ui/users/current')
    .then(response => {
        if (response.data.data.locale) {
            Vue.config.language = response.data.data.locale;
        }
    });

// import and register your component(s)
import Authent from './Authent/Authent.vue';
import Document from './SmartElement/SmartElement.vue';
import SEList from './SEList/seList.vue';
import DocumentTabs from './SETabs/seTabs.vue';
import AnakeenLoading from './AnakeenLoading/AnakeenLoading.vue';
import Logout from './Logout/Logout.vue';
import Identity from './Identity/Identity.vue';
import AuthentPassword from './Authent/AuthentPassword.vue'

import Dock from './Dock/Dock.vue';
import DockTab from './Dock/DockTab/DockTab.vue';

Vue.customElement('ank-loading', AnakeenLoading);
Vue.customElement('ank-authent', Authent);
Vue.customElement('ank-smart-element', Document);
Vue.customElement('ank-se-list', SEList);
Vue.customElement('ank-se-tabs', DocumentTabs);
Vue.customElement('ank-logout', Logout);
Vue.customElement('ank-identity', Identity);
Vue.customElement('ank-authent-password', AuthentPassword);

Vue.prototype.$dockEventBus = new Vue();
Vue.customElement('ank-dock', Dock);
Vue.customElement('ank-dock-tab', DockTab);
