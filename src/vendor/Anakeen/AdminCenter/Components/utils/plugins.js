import Vue from 'vue';

const PLUGIN_SCHEMA = {
    name: 'name',
    title: 'title',
    componentPath: 'componentPath',
    scriptURL: 'scriptURL',
    subcomponents: 'subcomponents',
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
    path: pluginDescription[PLUGIN_SCHEMA.componentPath],
    component: asyncVueComponent(pluginDescription),
});

export const asyncVueComponent = (pluginDescription) => () => ({
    component: new Promise((resolve, reject) => {
        if (!pluginDescription[PLUGIN_SCHEMA.scriptURL]) {
            reject("Invalid component url");
        } else {
            Vue.loadScript(pluginDescription[PLUGIN_SCHEMA.scriptURL])
                .then((response) => {
                    const component = Vue.component(pluginDescription[PLUGIN_SCHEMA.name]);
                    if (!component) {
                        reject(`Component "${pluginDescription[PLUGIN_SCHEMA.name]}" has not been registered correctly (global registration in Vue)`);
                    } else {
                        resolve(component);
                    }
                });
        }
    })
        .catch(err => {
            Vue.jQuery('#admin-center-notification')
                .data('kendoNotification')
                .show(`Impossible de charger le composant ${pluginDescription[PLUGIN_SCHEMA.title] || pluginDescription[PLUGIN_SCHEMA.name]}`, 'error');
            throw err;
        }),
});