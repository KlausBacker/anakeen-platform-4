import AnkAxios from "../Components/utils/axios";

export default (Vue, options) => {
    Vue.use(AnkAxios);
    Vue.jQuery = Vue.jquery = Vue.prototype.$ = kendo.jQuery;
    Vue.kendo = Vue.prototype.$kendo = kendo;
};