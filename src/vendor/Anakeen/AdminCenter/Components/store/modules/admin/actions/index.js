import { mutationsType } from "../mutations";
import Vue from 'vue';

export default {
    loadModulesList ({ commit }) {
        Vue.ankApi.get('admin/modules')
            .then(response => {
                if (response.status === 200 && response.statusText === 'OK') {
                    commit(mutationsType.SET_MODULES, response.data);
                } else {
                    throw "Unable to get modules list";
                }
            });
    }
};