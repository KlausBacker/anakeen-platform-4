const packageInfo = require("../package.json");
import Vue from 'vue'
import { Mixins } from "vue-property-decorator";
import HubStation from "./HubStation/HubStation.vue"
import HubElement from "./HubElement/HubElement.vue"
import ElementMixin from "./HubElement/Mixins/HubElementMixin";
const HubElementMixin = Mixins(ElementMixin);
export {
    HubStation,
    HubElement,
    HubElementMixin
};

export function install(vue: typeof Vue) {
    vue.component(`hub-station`, HubStation);
}


export default {
    install,
    version: packageInfo.version
}