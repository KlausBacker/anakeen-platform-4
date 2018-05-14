import router from '../../../../router';
import Vue from "vue";

export const mutationsType = {
    SET_PLUGINS: 'SET_PLUGINS',
    UPDATE_ROUTER: 'UPDATE_ROUTER'
};

export default {
    [mutationsType.SET_PLUGINS] (state, plugins) {
        state.plugins = plugins;
    },
    [mutationsType.UPDATE_ROUTER] (state, plugins) {
        router.addRoutes(plugins.map(plugin => {
            return {
                path: plugin.componentPath,
                component: () => {
                    return new Promise((resolve, reject) => {
                        Vue.loadScript(plugin.url)
                            .then((response) => {
                                resolve({
                                    template: `<${plugin.name}></${plugin.name}>`,
                                });
                            })
                            .catch((err) => {
                                console.error(err);
                            });
                    });
                }
            };
        }));
    }
};