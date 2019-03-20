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
import AdminCenterAuthentTokens from "../../AuthenticationTokens/AuthenticationTokens";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-hub-authentication-tokens",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-authentication-tokens": AdminCenterAuthentTokens
  },
  watch: {
    selectedToken(newValue) {
      if (this.isHubContent) {
        this.navigate(this.routeUrl() + "/" + newValue);
      }
    }
  },
  created() {
    if (this.isHubContent) {
      this.subRouting();
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
          this.hubNotify({
            type: "info", // Type de notification parmi: "info", "notice", "success", "warning", "error"
            content: {
              textContent:
                "You see the info of the token " +
                params.tokenId +
                " : " +
                this.routeUrl(), // ou htmlContent: "<em>Un message d'information important</em>"
              title: "VAULTS"
            },
            options: {
              displayTime: 5000, // temps d'affichage en ms de la notification (5000ms par défaut)
              closable: false // La notification peut être fermée via l'ui ou non (true par défaut)
            }
          });
          this.selectedToken = params.tokenId;
        }).resolve(window.location.pathname);
      }
    };
  }
};
</script>
