import { mutationsType } from "../mutations";
import Vue from 'vue';

export default {
    loadUser ({ commit }) {
        Vue.ankApi.get('admin/user')
            .then(response => {
                if (response.status === 200 && response.statusText === 'OK') {
                    commit(mutationsType.SET_USER, response.data);
                } else {
                    throw "Unable to get current user informations";
                }
            });
    },
    showMessage ({ commit }, message) {
        const notif = Vue.jQuery("#admin-center-notification").data('kendoNotification');
        notif.show(message.content, message.type);
    },
    logout ({ commit }) {
        return Vue.ankApi.delete('authent/sessions/current')
            .then(response => {
                if (response.status === 200) {
                    commit(mutationsType.SET_USER, {});
                } else {
                    throw "An error occured during logout operation";
                }
            });
    },
};