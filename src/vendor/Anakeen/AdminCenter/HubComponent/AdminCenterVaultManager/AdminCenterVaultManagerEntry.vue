<template>
    <div>
        <nav v-if="isDockCollapsed">
            <i class="material-icons hub-icon">storage</i>
        </nav>
        <nav v-else-if="isDockExpanded">
            <i class="material-icons hub-icon">storage</i> <span> Vault Manager</span>
        </nav>
        <div v-else-if="isHubContent" class="vault-manager">
            <admin-center-vault></admin-center-vault>
        </div>
    </div>
</template>
<script>
  import VaultManager from "../../VaultManager/VaultManager.vue";
  import { HubElement } from "@anakeen/hub-components";

  export default {
    name: "ank-admin-vault-manager",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "admin-center-vault": VaultManager
    }
  }
</script>