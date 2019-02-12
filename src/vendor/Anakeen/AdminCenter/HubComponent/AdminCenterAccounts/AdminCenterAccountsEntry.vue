<template>
    <div>
        <div v-if="isDockCollapsed">
            <i class="fa fa-users"></i>
        </div>
        <div v-else-if="isDockExpanded">
            <span><i class="fa fa-users"></i> Accounts</span>
        </div>
        <div v-else-if="isHubContent" class="account-station">
            <admin-center-account></admin-center-account>
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
<style>
    .account-station {
        height: 100%;
        width: 100%;
    }
</style>