<template>
  <div>
    <nav v-if="isDockCollapsed">
      <i class="material-icons hub-icon">settings</i>
    </nav>
    <nav v-else-if="isDockExpanded">
      <i class="material-icons hub-icon">settings</i><span> Parameters</span>
    </nav>
    <div v-else-if="isHubContent" class="parameters-parent">
      <admin-center-parameters></admin-center-parameters>
    </div>
  </div>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";
import Vue from "vue";

export default {
  name: "ank-admin-parameter",
  extends: HubElement, // ou mixins: [ HubElementMixins ],

  created() {
    if (this.isHubContent) {
      Vue.component("admin-center-parameters", resolve => {
        import("../../Parameters/AdminCenterParameters.vue").then(Component => {
          resolve(Component.default);
        });
      });
    }
  }
};
</script>
<style>
.parameters-parent {
  height: 100%;
}
</style>