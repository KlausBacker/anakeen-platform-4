<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">settings</i>
      <span v-if="!isDockCollapsed"> Parameters</span>
    </nav>
    <template v-slot:hubContent>
      <div class="parameters-parent">
        <admin-center-parameters></admin-center-parameters>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHub.esm";

export default {
  name: "ank-admin-parameter",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-parameters": resolve =>
      import("../../Parameters/AdminCenterParameters.vue").then(Component => {
        resolve(Component.default);
      })
  }
};
</script>
<style>
.parameters-parent {
  height: 100%;
}
</style>
