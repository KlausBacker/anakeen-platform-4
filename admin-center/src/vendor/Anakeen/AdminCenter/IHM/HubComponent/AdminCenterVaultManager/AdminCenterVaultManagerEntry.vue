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
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-vault-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-vault": () =>
      new Promise(resolve => {
        import("../../VaultManager/VaultManager.vue").then(Component => {
          resolve(Component.default);
        });
      })
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
        const url = (this.routeUrl() + "/:vaultId").replace(/\/\/+/g, "/");

        this.registerRoute(url, params => {
          this.selectedVault = params.vaultId;
        }).resolve(window.location.pathname);
      }
    };
  }
};
</script>
