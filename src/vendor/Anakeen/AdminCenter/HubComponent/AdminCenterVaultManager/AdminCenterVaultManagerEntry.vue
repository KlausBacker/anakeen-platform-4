<template>
    <div>
        <div v-if="isDockCollapsed">
            <i class="fa fa-hdd-o"></i>
        </div>
        <div v-else-if="isDockExpanded">
            <span><i class="fa fa-hdd-o"></i> Vault Manager</span>
        </div>
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