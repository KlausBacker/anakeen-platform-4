import { mutationsType } from "../mutations";
import Vue from 'vue';

export default {
    loadPluginsList ({ commit }) {
        Vue.ankApi.get('admin/plugins')
            .then(response => {
                if (response.status === 200 && response.statusText === 'OK') {
                    commit(mutationsType.SET_PLUGINS, response.data);
                    commit(mutationsType.UPDATE_ROUTER, response.data);
                } else {
                    throw "Unable to get modules list";
                }
            });
    },
};