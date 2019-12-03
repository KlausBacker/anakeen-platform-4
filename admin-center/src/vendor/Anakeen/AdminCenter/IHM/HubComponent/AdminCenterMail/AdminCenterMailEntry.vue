<template>
  <hub-element-layout>
    <nav>
      <i class="fa fa-envelope mail-icon" aria-hidden="true"></i>
      <span v-if="!isDockCollapsed">Mail Templates</span>
    </nav>
    <template v-slot:hubContent>
      <div class="mail-station">
        <admin-center-mail></admin-center-mail>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-mail-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-mail": () =>import("../../Mail/AdminCenterMail.vue")
  }
};
</script>
<style>
.mail-station {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  height: 100%;
}

.mail-icon {
  font-size: 24px;
  margin-right: 0.5rem;
}
</style>
