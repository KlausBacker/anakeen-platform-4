import Vue from 'vue'
import { Mixins } from "vue-property-decorator";
import HubStation from "./HubStation/HubStation.vue"
import ElementMixin from "./Mixins/HubElement/HubElementMixin";
const HubElementMixin = Mixins(ElementMixin);
export {
    HubStation,
    HubElementMixin
};

export function install(vue: typeof Vue) {
    vue.component(`hub-station`, HubStation);
}


export default {
    install
}