import Vue from "vue";
import Enum from "./EnumDevCenter.vue";
import Axios from "axios";

Vue.prototype.$http = Axios.create();

export default {
  label: "Enums",
  name: "enumDevCenter",
  path: "enums",
  component: Enum
};
