<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">settings</i>
      <span v-if="!isDockCollapsed">{{ $t("globalParameter.Title Parameters")}}</span>
    </nav>
    <template v-slot:hubContent>
      <div class="parameters-parent">
        <admin-center-parameters
          :user-id="userId"
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
  props: ["hasUsers", "hasGlobal", "namespace", "specificUser"],
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
      userId: "",
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
        this.userId = this.specificUser;
      }
    },
    registerRouterPattern () {
      const parametersPattern = [{ pattern: "/global/", global: true }, { pattern: "/global/:paramId", global: true }, { pattern: "/user/" }, { pattern: "/user/:userId" }, { pattern: "/user/:userId/:paramId" }];
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
            if (params && params.userId && !this.specificUser) {
              this.userId = params.userId;
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
