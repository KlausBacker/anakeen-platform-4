import Vue from "vue";
import Role from "./RoleDevCenter.vue";
import Axios from "axios";

Vue.prototype.$http = Axios.create();

export default {
  label: "Roles",
  name: "roleDevCenter",
  path: "roles",
  component: Role
};
