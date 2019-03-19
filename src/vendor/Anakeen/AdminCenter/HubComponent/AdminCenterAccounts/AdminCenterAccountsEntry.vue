<template>
    <div>
        <nav v-if="isDockCollapsed">
            <i class="material-icons hub-icon">group</i>
        </nav>
        <nav v-else-if="isDockExpanded" >
            <i class="material-icons hub-icon">group</i> <span>Accounts</span>
        </nav>
        <div v-else-if="isHubContent" class="account-station">
            <admin-center-account></admin-center-account>
        </div>
    </div>
</template>
<script>
import AdminCenterAccount from "../../Account/AdminCenterAccount.vue";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-account",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    AdminCenterAccount
  }
};
</script>
<style>
.account-station {
  height: 100%;
  width: 100%;
}
</style>
