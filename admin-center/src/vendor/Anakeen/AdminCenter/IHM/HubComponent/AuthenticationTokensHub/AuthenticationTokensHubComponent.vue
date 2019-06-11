<template>
  <div>
    <nav v-if="isDockCollapsed">
      <i class="material-icons hub-icon">fingerprint</i>
    </nav>
    <nav v-else-if="isDockExpanded">
      <i class="material-icons hub-icon">fingerprint</i>
      <span> Authentication Tokens</span>
    </nav>
    <div v-else-if="isHubContent" class="token-station">
      <admin-center-authentication-tokens v-model="selectedToken" />
    </div>
  </div>
</template>
<script>
// import AdminCenterAuthentTokens from "../../AuthenticationTokens/AuthenticationTokens";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import Vue from "vue";

export default {
  name: "ank-hub-authentication-tokens",
  extends: HubElement, // ou mixins: [ HubElementMixins ],

  watch: {
    selectedToken(newValue) {
      if (this.isHubContent) {
        this.navigate(this.routeUrl() + "/" + newValue);
      }
    }
  },
  created() {
    if (this.isHubContent) {
      Vue.component("admin-center-authentication-tokens", resolve => {
        import("../../AuthenticationTokens/AuthenticationTokens").then(
          Component => {
            resolve(Component.default);

            this.subRouting();
          }
        );
      });
    }
  },
  data() {
    return {
      selectedToken: "",
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      subRouting: () => {
        const url = (this.routeUrl() + "/:tokenId").replace(/\/\/+/g, "/");

        this.registerRoute(url, params => {
          this.selectedToken = params.tokenId;
        }).resolve(window.location.pathname);
      }
    };
  }
};
</script>
