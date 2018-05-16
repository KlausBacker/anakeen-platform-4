import Vue from 'vue';
import store from '../store';

const PLUGIN_SCHEMA = {
    name: 'name',
    title: 'title',
    pluginPath: 'pluginPath',
    scriptURL: 'scriptURL',
    subcomponents: 'subcomponents',
    pluginTemplate: 'pluginTemplate'
};

const attachPluginEvents = (element) => {
    element.addEventListener('ank-admin-notify', (event) => {
        const message = event.detail && event.detail.length ? event.detail[0] : null;
        if (message) {
            store.dispatch('showMessage', message);
        }
    });
};

export const buildVueRoutes = (plugins) => {
    let routes = [];
    plugins.forEach((pluginDescription) => {
        routes.push(buildVueRouteObject(pluginDescription));
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

export const asyncVueComponent = (pluginDescription) => () => ({
    component: new Promise((resolve, reject) => {
            if (!pluginDescription[PLUGIN_SCHEMA.scriptURL]) {
                reject("Invalid component url");
            } else {
                Vue.loadScript(pluginDescription[PLUGIN_SCHEMA.scriptURL])
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
});