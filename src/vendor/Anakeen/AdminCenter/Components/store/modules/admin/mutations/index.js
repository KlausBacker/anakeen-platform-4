import router from '../../../../router';
import { buildVueRoutes } from "../../../../utils/plugins";

export const mutationsType = {
    SET_PLUGINS: 'SET_PLUGINS',
    UPDATE_ROUTER: 'UPDATE_ROUTER'
};

export default {
    [mutationsType.SET_PLUGINS] (state, plugins) {
        state.plugins = plugins;
    },
    [mutationsType.UPDATE_ROUTER] (state, plugins) {
        const routes = buildVueRoutes(plugins);
        router.addRoutes(routes);
    }
};