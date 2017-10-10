import Vue from 'vue';
import axios from 'axios';

import 'document-register-element/build/document-register-element';

// include vue-custom-element plugin to Vue
import VueCustomElement from 'vue-custom-element';

import GetTextPlugin from 'vue-gettext';
import translations from './Authent/translation.json';

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
Vue.prototype.$http = axios.create({
  baseURL: '/api/v1'
});
Vue.prototype.$kendo = kendo;

// import and register your component(s)
import Authent from './Authent/Authent.vue';
import Document from './Document/Document.vue';

Vue.customElement('a4-authent', Authent);
Vue.customElement('a4-document', Document);
