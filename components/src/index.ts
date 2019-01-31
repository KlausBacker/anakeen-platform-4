import Vue from 'vue'
import HubStation from "./HubStation/HubStation.vue"
import HubElement from "./HubElement/HubElement.vue"

export { HubStation, HubElement }

export function install(vue: typeof Vue) {
    vue.component(`hub-station`, HubStation);
    vue.component(`hub-element`, HubElement);
}


export default {
    install
}