<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">fingerprint</i
      ><span v-if="!isDockCollapsed"> Authentication Tokens</span>
    </nav>
    <template v-slot:hubContent>
      <div class="token-station">
        <admin-center-authentication-tokens v-model="selectedToken" />
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
// import AdminCenterAuthentTokens from "../../AuthenticationTokens/AuthenticationTokens";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import Vue from "vue";

export default {
  name: "ank-hub-authentication-tokens",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-authentication-tokens": resolve => {
      import("../../AuthenticationTokens/AuthenticationTokens").then(
        Component => {
          resolve(Component.default);
        }
      );
    }
  },
  watch: {
    selectedToken(newValue) {
      this.navigate(this.routeUrl() + "/" + newValue);
    }
  },
  created() {
    this.subRouting();
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
