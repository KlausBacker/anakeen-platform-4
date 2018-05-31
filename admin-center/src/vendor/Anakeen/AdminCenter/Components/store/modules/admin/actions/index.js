import { mutationsType } from "../mutations";
import { mutationsType as AppMutationsType } from '../../application/mutations';
import Vue from 'vue';

export default {
    loadPluginsList ({ commit }) {
        Vue.ankAdmin.get('plugins')
            .then(response => {
                if (response.status === 200 && response.statusText === 'OK') {
                    const data = response.data;
                    commit(AppMutationsType.SET_APPNAME, data.appName);
                    commit(mutationsType.SET_PLUGINS, data.plugins);
                    commit(mutationsType.UPDATE_ROUTER, data.plugins);
                } else {
                    throw "Unable to get modules list";
                }
            });
    },
};