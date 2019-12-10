<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">group</i>
      <span v-if="!isDockCollapsed">Accounts</span>
    </nav>
    <template v-slot:hubContent>
      <div class="account-station">
        <admin-center-account></admin-center-account>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";

export default {
  name: "ank-admin-account",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-account": () => import("../../Account/AdminCenterAccount.vue")
  },
  data() {
    return {
      counter: 0
    };
  }
};
</script>
<style>
.account-station {
  height: 100%;
  width: 100%;
}
</style>
