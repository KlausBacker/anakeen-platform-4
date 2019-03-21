<template>
    <div>
        <nav v-if="isDockCollapsed">
            <i class="material-icons hub-icon">file_copy</i>
        </nav>
        <nav v-else-if="isDockExpanded">
            <i class="material-icons hub-icon">file_copy</i> <span> Transformation File Manager</span>
        </nav>
        <div v-else-if="isHubContent" class="hub-te-manager">
            <admin-center-vault></admin-center-vault>
        </div>
    </div>
</template>
<script>
  import TeManager from "../TeManager/TeManager.vue";
  import HubElement  from "@anakeen/hub-components/components/lib/HubElement";

  export default {
    name: "ank-admin-te-manager",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      "admin-center-vault": TeManager
    }
  }
</script>
<style>
    .hub-te-manager {
        height:100%;
    }
</style>