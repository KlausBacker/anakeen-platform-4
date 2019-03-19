<template>
    <div>
        <nav v-if="isDockCollapsed">
            <i class="material-icons hub-icon">fingerprint</i>
        </nav>
        <nav v-else-if="isDockExpanded">
            <i class="material-icons hub-icon">fingerprint</i><span> Authentication Tokens</span>
        </nav>
        <div v-else-if="isHubContent" class="token-station">
            <admin-center-authentication-tokens v-model="selectedToken"></admin-center-authentication-tokens>
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
        this.navigate(`/admin/tokens/${newValue.tokenId || newValue}`);
      }
    },
    created() {
      this.registerRoute("/admin/tokens/:tokenId", (params) => {
          this.hubNotify({
            type: "info", // Type de notification parmi: "info", "notice", "success", "warning", "error"
            content: {
              textContent: "You see the info of the token "+params.tokenId, // ou htmlContent: "<em>Un message d'information important</em>"
              title: "TOKENS",
            },
            options: {
              displayTime: 5000, // temps d'affichage en ms de la notification (5000ms par défaut)
              closable: false, // La notification peut être fermée via l'ui ou non (true par défaut)
            }
          });
          this.selectedToken = params.tokenId;
        }).resolve(window.location.pathname);
    },
    data() {
      return {
        selectedToken: ""
      }
    }

  }
</script>