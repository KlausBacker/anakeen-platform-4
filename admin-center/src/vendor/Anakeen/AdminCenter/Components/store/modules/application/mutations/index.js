export const mutationsType = {
    SET_USER: 'SET_USER'
};

export default {
    [mutationsType.SET_USER] (state, user) {
        state.user = user;
    },
};