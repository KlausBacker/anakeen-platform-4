export const mutationsType = {
    SET_MODULES: 'SET_MODULES'
};

export default {
    [mutationsType.SET_MODULES] (state, modules) {
        state.modules = modules;
    },
};