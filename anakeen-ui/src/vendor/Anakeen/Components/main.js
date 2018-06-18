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
    .then(response =>  {
        if (response.data.data.locale) {
            const sanitizedLanguage = response.data.data.locale.split('.');
            Vue.config.language = sanitizedLanguage[0];
        }
    })
    .catch(err => {
        console.error(err);
    });

// import and register your component(s)
import Authent from './Authent/Authent.vue?shadow';
import AuthentPassword from './Authent/AuthentPassword.vue?shadow';
import Document from './SmartElement/SmartElement.vue?shadow';
import SEList from './SEList/seList.vue?shadow';
import DocumentTabs from './SETabs/seTabs.vue?shadow';
import AnakeenLoading from './AnakeenLoading/AnakeenLoading.vue?shadow';
import Logout from './Logout/Logout.vue?shadow';
import Identity from './Identity/Identity.vue?shadow';

import Dock from './Dock/Dock.vue?shadow';
import DockTab from './Dock/DockTab/DockTab.vue?shadow';
import wrap from '@vue/web-component-wrapper';

window.customElements.define('ank-loading', wrap(Vue, AnakeenLoading));
window.customElements.define('ank-authent', wrap(Vue, Authent));
window.customElements.define('ank-authent-password', wrap(Vue, AuthentPassword));
window.customElements.define('ank-smart-element', wrap(Vue, Document));
window.customElements.define('ank-se-list', wrap(Vue, SEList));
window.customElements.define('ank-se-tabs', wrap(Vue, DocumentTabs));
window.customElements.define('ank-logout', wrap(Vue, Logout));
window.customElements.define('ank-identity', wrap(Vue, Identity));

Vue.prototype.$dockEventBus = new Vue();
window.customElements.define('ank-dock', wrap(Vue, Dock));
window.customElements.define('ank-dock-tab', wrap(Vue, DockTab));
