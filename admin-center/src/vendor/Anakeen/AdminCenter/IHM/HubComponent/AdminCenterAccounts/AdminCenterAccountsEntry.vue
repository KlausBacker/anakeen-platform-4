<template>
  <hub-element-layout>
    <nav v-if="isDockCollapsed">
      <i class="material-icons hub-icon">group</i>
    </nav>
    <nav v-else>
      <i class="material-icons hub-icon">group</i> <span>Accounts</span>
    </nav>
    <div slot="hubContent" class="account-station">
      <admin-center-account></admin-center-account>
    </div>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-account",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-account": () =>
      new Promise(resolve => {
        import("../../Account/AdminCenterAccount.vue").then(Component => {
          resolve(Component.default);
        });
      })
  },
  updated() {
    console.log("updated");
  },
  watch: {
    isDockCollapsed(value) {
      console.log("collapsed", value);
    }
  }
};
</script>
<style>
.account-station {
  height: 100%;
  width: 100%;
}
</style>
