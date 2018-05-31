export const mutationsType = {
    SET_USER: 'SET_USER',
    SET_APPNAME: 'SET_APPNAME'
};

export default {
    [mutationsType.SET_APPNAME] (state, appName) {
        state.appName = appName;
    },
    [mutationsType.SET_USER] (state, user) {
        state.user = user;
    },
};