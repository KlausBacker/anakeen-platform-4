// const modulesFiles = require.context('./', true, /\.js$/);

export default {
    application: {
        state: require('./application/state').default,
        getters: require('./application/getters').default,
        mutations: require('./application/mutations').default,
        actions: require('./application/actions').default
    }
};