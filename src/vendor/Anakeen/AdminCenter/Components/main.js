import Vue from 'vue';
import LoadScript from 'vue-plugin-load-script';
import store from './store';
import router from './router';
import AdminCenter from './AdminCenter.vue';
import httpApi from './utils/apiConf';
import httpAdmin from './utils/adminConf';
import httpAxios from './utils/axiosBase';

Vue.use(LoadScript);
Vue.ankApi = Vue.prototype.$ankApi = httpApi;
Vue.ankAdmin = Vue.prototype.$ankAdmin = httpAdmin;
Vue.axios = Vue.prototype.$axios = httpAxios;
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