<template>
  <hub-element-layout>
    <nav v-if="isDockCollapsed">
      <i class="fa fa-envelope mail-icon" aria-hidden="true"></i>
    </nav>
    <nav v-else>
      <i class="fa fa-envelope mail-icon" aria-hidden="true"></i> <span>Mail Templates</span>
    </nav>
    <div slot="hubContent" class="mail-station">
      <admin-center-mail></admin-center-mail>
    </div>
  </hub-element-layout>
</template>
<script>
import Vue from "vue";
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-mail-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-mail": () =>
      new Promise(resolve => {
        import("../../Mail/AdminCenterMail.vue").then(Component => {
          resolve(Component.default);
        });
      })
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
