import axios, {AxiosInstance, AxiosRequestConfig} from "axios";
import {VueConstructor} from "vue";
import ErrorManager from "./utils/ErrorManager";

interface IAxiosInstance extends AxiosInstance {
    errorEvents: ErrorManager
}

interface IVueAxiosConfig {
    axios: AxiosRequestConfig,
    vueInjection: string;
}

export default function install(Vue: VueConstructor, options: IVueAxiosConfig) {
    const vueInject = options ? options.vueInjection || "$http" : "$http";
    if (!Vue.prototype[vueInject]) {
        let axiosConfig;
        if (options && options.axios) {
            axiosConfig = options.axios;
        }
        const axiosInstance = axios.create(axiosConfig) as IAxiosInstance;
        axiosInstance.errorEvents = new ErrorManager(axiosInstance);
        Vue.prototype[vueInject] = axiosInstance;
    } else if (Vue.prototype[vueInject].constructor === axios.constructor) {
        Vue.prototype[vueInject].errorEvents = new ErrorManager(Vue.prototype[vueInject]);
    }
}