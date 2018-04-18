import Vue from 'vue';
import store from './store';
import router from './router';
import AdminCenter from './AdminCenter.vue';
import httpApi from './utils/apiConf';
import httpAdmin from './utils/adminConf';

Vue.ankApi = Vue.prototype.$ankApi = httpApi;
Vue.ankAdmin = Vue.prototype.$ankAdmin = httpAdmin;
Vue.kendo = Vue.prototype.$kendo = kendo;

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