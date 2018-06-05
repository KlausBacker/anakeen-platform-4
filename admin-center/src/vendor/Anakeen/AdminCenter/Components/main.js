import Vue from 'vue';
import LoadScript from 'vue-plugin-load-script';
import store from './store';
import router from './router';
import AdminCenter from './AdminCenter.vue';
import AnkAxios from './utils/axios';

Vue.use(LoadScript);
Vue.use(AnkAxios, {
});
Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
Vue.kendo = Vue.prototype.$kendo = kendo;

window.Vue = Vue;
new Vue({
    el: "#admin-center",
    template:
        '<admin-center/>',
    components: {
        AdminCenter,
    },
    store,
    router
});