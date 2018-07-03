import Vue from 'vue';
import store from '../store';

export const PLUGIN_SCHEMA = {
    name: 'name',
    title: 'title',
    pluginPath: 'pluginPath',
    scriptURL: 'scriptURL',
    debugScriptURL: 'debugScriptURL',
    subcomponents: 'sublevel',
    pluginTemplate: 'pluginTemplate',
    icon: 'icon',
    autoselect: 'autoselect',
};

const attachPluginEvents = (element) => {
    element.addEventListener('ank-admin-notify', (event) => {
        const message = event.detail && event.detail.length ? event.detail[0] : null;
        if (message) {
            store.dispatch('showMessage', message);
        }
    });

    element.addEventListener('ank-admin-modal', (event) => {
        const modalConfig = event.detail && event.detail.length ? event.detail[0] : null;
        if (modalConfig) {
            store.dispatch('showModal', modalConfig);
        }
    });
};

export const buildVueRoutes = (plugins) => {
    let routes = [];
    plugins.forEach((pluginDescription) => {
        if (pluginDescription[PLUGIN_SCHEMA.pluginPath]) {
            routes.push(buildVueRouteObject(pluginDescription));
        }
        // If plugin have sublevel components
        if (pluginDescription[PLUGIN_SCHEMA.subcomponents] && pluginDescription[PLUGIN_SCHEMA.subcomponents].length) {
            routes = routes.concat(buildVueRoutes(pluginDescription[PLUGIN_SCHEMA.subcomponents]));
        }
    });
    return routes;
};

export const buildVueRouteObject = (pluginDescription) => ({
    path: pluginDescription[PLUGIN_SCHEMA.pluginPath],
    component: asyncVueComponent(pluginDescription),
});

export const asyncVueComponent = (pluginDescription) => () => {
    return {
        component: new Promise((resolve, reject) => {
            let scriptURL = "";
            if (process.env.NODE_ENV === "debug" && pluginDescription[PLUGIN_SCHEMA.debugScriptURL]) {
                scriptURL = pluginDescription[PLUGIN_SCHEMA.debugScriptURL];
            } else {
                scriptURL = pluginDescription[PLUGIN_SCHEMA.scriptURL];
            }
            if (!scriptURL && (!pluginDescription[PLUGIN_SCHEMA.subcomponents] || !pluginDescription[PLUGIN_SCHEMA.subcomponents].length)) {
                reject("Invalid component url");
            } else if (scriptURL) {
                // Test network access to script with axios to handle network errors
                Vue.axios.get(scriptURL).then(() => {
                    Vue.loadScript(scriptURL)
                        .then(() => {
                            const componentTemplate = pluginDescription[PLUGIN_SCHEMA.pluginTemplate];
                            if (!componentTemplate) {
                                reject(`Component "${pluginDescription[PLUGIN_SCHEMA.name]}" has not a valid template`);
                            } else {
                                resolve({
                                    template: componentTemplate,
                                    mounted() {
                                        attachPluginEvents(this.$el);
                                    }
                                });
                            }
                        })
                        .catch(err => {
                            reject(err);
                        });
                }).catch(reject);

            } else {
                resolve({});
            }
        })
            .catch(err => {
                store.dispatch('showMessage', {
                    content: {
                        title: "Erreur de chargement",
                        message: `Impossible de charger le composant ${pluginDescription[PLUGIN_SCHEMA.title] ||
                        pluginDescription[PLUGIN_SCHEMA.name]}`
                    },
                    type: 'admin-error'
                });
                throw err;
            }),
    };
};