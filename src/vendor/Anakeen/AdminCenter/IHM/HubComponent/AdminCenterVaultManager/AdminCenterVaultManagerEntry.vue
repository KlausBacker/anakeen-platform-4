<template>
  <div>
    <nav v-if="isDockCollapsed">
      <i class="material-icons hub-icon">storage</i>
    </nav>
    <nav v-else-if="isDockExpanded">
      <i class="material-icons hub-icon">storage</i> <span> Vault Manager</span>
    </nav>
    <div v-else-if="isHubContent" class="vault-manager">
      <admin-center-vault v-model="selectedVault"></admin-center-vault>
    </div>
  </div>
</template>
<script>
import VaultManager from "../../VaultManager/VaultManager.vue";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-vault-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-vault": VaultManager
  },

  watch: {
    selectedVault(newValue) {
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
      selectedVault: "",
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      subRouting: () => {
        const url = (this.routeUrl() + "/:vaultId").replace(/\/\/+/g, '/');

        this.registerRoute(url, params => {
          this.hubNotify({
            type: "info", // Type de notification parmi: "info", "notice", "success", "warning", "error"
            content: {
              textContent: "You see the info of the vault " + params.vaultId+ " : "+this.routeUrl() , // ou htmlContent: "<em>Un message d'information important</em>"
              title: "VAULTS"
            },
            options: {
              displayTime: 5000, // temps d'affichage en ms de la notification (5000ms par défaut)
              closable: false // La notification peut être fermée via l'ui ou non (true par défaut)
            }
          });
          this.selectedVault = params.vaultId;
        }).resolve(window.location.pathname);
      }
    };
  }
};
</script>
