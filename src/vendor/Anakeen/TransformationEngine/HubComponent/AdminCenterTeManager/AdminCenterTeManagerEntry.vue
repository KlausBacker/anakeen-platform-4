<template>
  <div>
    <nav v-if="isDockCollapsed">
      <i class="material-icons hub-icon">file_copy</i>
    </nav>
    <nav v-else-if="isDockExpanded">
      <i class="material-icons hub-icon">file_copy</i>
      <span> Transformation File Manager</span>
    </nav>
    <div v-else-if="isHubContent" class="hub-te-manager">
      <admin-center-te></admin-center-te>
    </div>
  </div>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import Vue from "vue";

export default {
  name: "ank-admin-te-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],

  created() {
    if (this.isHubContent) {
      Vue.component("admin-center-te", resolve => {
        import("../TeManager/TeManager.vue").then(Component => {
          resolve(Component.default);
        });
      });
    }
  }
};
</script>
<style>
.hub-te-manager {
  height: 100%;
}
</style>
