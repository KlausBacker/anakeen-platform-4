<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">storage</i>
      <span v-if="!isDockCollapsed"> Vault Manager</span>
    </nav>
    <template v-slot:hubContent>
      <div class="vault-manager">
        <admin-center-vault v-model="selectedVault"></admin-center-vault>
      </div>
    </template>
  </hub-element-layout>
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
      this.navigate(this.routeUrl() + "/" + newValue);
    }
  },

  created() {
    this.subRouting();
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
