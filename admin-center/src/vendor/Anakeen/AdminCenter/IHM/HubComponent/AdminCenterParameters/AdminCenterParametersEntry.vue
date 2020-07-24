<template>
  <hub-element-layout>
    <nav>
      <i v-if="icon" class="material-icons hub-icon">{{ icon }}</i>
      <i v-else class="material-icons hub-icon">settings</i>
      <span v-if="!isDockCollapsed && label">{{ label }}</span>
      <span v-else-if="!isDockCollapsed">{{ this.$t("AdminCenterAllParameter.Title Parameters") }}</span>
    </nav>
    <template v-slot:hubContent>
      <div class="parameters-parent">
        <admin-center-parameters
          :user-login="userLogin"
          :param-id="paramId"
          :is-global-tab="isGlobalTab"
          :is-user-tab="isUserTab"
          :selected-tab="selectedTab"
          @navigate="onNavigate"
          @notify="handleNotification"
          v-bind="$props"
        ></admin-center-parameters>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
import { Watch } from "vue-property-decorator";

export default {
  name: "ank-admin-parameter",
  extends: HubElement,
  props: ["hasUsers", "hasGlobal", "namespace", "specificUser", "icon", "label"],
  components: {
    "admin-center-parameters": resolve => import("../../Parameters/AdminCenterParameters.vue").then(Component => {
      resolve(Component.default);
    })
  },
  computed: {
    routeUrl () {
      return "/" + this.entryOptions.route;
    }
  },
  data () {
    return {
      userLogin: "",
      paramId: "",
      selectedTab: "globalTab",
      isGlobalTab: true,
      isUserTab: true,
      url: ""
    }
  },
  created () {
    this.paramId = "";
    this.isGlobalTab = this.hasGlobal;
    this.isUserTab = this.hasUsers;
    this.isSpecificUser();
    this.registerRouterPattern();
  },
  methods: {
    isSpecificUser () {
      if (this.specificUser) {
        this.userLogin = this.specificUser;
      }
    },
    registerRouterPattern () {
      const parametersPattern = [{ pattern: "/global/", global: true }, { pattern: "/global/:paramId", global: true }, { pattern: "/user/" }, { pattern: "/user/:userLogin" }, { pattern: "/user/:userLogin/:paramId" }];
      const routesHandlers = parametersPattern.reduce((acc, curr) => {
        acc[this.routeUrl + curr.pattern] = this.onParameterRouteChanged(!!curr.global);
        return acc;
      }, {});
      this.getRouter().on(routesHandlers).resolve();
    },
    onParameterRouteChanged (isGlobal) {
      if (this.isGlobalTab === true) {
        this.selectedTab = "globalTab";
      } else {
        this.selectedTab = "userTab";
      }
      return (params) => {
        if (isGlobal) {
          this.selectedTab = "globalTab";
          if (this.isGlobalTab === true) {
            if (params && params.paramId) {
              this.paramId = params.paramId;
            }
          }
        } else {
          if (this.isUserTab === true) {
            this.selectedTab = "userTab";
            if (params && params.userLogin && !this.specificUser) {
              this.userLogin = params.userLogin;
              if (params && params.paramId) {
                this.paramId = params.paramId;
              }
            }
          }
        }
      }
    },
    handleNotification (typeNotification, message) {
      this.hubNotify({
        type: typeNotification,
        content: {
          textContent: message,
          title: typeNotification
        }
      });
    },
    onNavigate (route) {
      const url = ("/admin/" + this.routeUrl + "/" + route).replace(/\/\/+/g, "/");
      this.navigate(url, true, { silent: true });
    }
  }
};
</script>
<style>
.parameters-parent {
  height: 100%;
}
</style>
