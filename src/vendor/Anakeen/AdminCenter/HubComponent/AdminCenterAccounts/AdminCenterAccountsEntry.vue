<template>
    <div>
        <div v-if="isDockCollapsed">
            <i class="fa fa-users"></i>
        </div>
        <div v-else-if="isDockExpanded">
            <span>Admin Center Accounts</span>
        </div>
        <div v-else-if="isHubContent">
            <ank-admin-account></ank-admin-account>
        </div>
    </div>
</template>
<script>
  import AdminCenterAccount from "../../Account/AdminCenterAccount.vue";
  import { HubElement } from "@anakeen/hub-components";

  export default {
    name: "ank-admin-account",
    extends: HubElement, // ou mixins: [ HubElementMixins ],
    components: {
      AdminCenterAccount
    }
  }
</script>