// jscs:disable disallowFunctionDeclarations
import AnkMixin from './AnkVueComponentMixin';

export {
    AnkMixin
};

export default function install(Vue, options) {
    // Install globally on all Vue components, prefer use local Mixin
    Vue.mixin(AnkMixin);
};
