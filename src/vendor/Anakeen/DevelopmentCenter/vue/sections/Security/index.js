import Vue from "vue";
import Security from "./SecurityDevCenter";
import Routes from "../Routes/RoutesDevCenter";
import Roles from "../Role/RoleDevCenter";
import RoutesAcl from "../Routes/RoutesAcl/RoutesAcl";
import AclAccount from "../Routes/RoutesPermissions/RoutesPermissions";
import Axios from "axios";

Vue.prototype.$http = Axios.create();

export default {
  label: "Security",
  name: "securityDevCenter",
  path: "security",
  component: Security,
  children: [
    {
      name: "Security::Routes",
      path: "routes",
      component: Routes,
      children: [
        {
          name: "Security::Routes::RoutesAcl",
          path: "access/controls/",
          component: RoutesAcl
        },
        {
          name: "Security::Routes::RoutesPermissions",
          path: "access/permissions/",
          component: AclAccount
        }
      ]
    },
    {
      name: "Security::Roles",
      path: "roles",
      component: Roles
    }
  ]
};
