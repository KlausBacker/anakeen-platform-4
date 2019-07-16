<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">file_copy</i> <span v-if="!isDockCollapsed"> Transformation File Manager</span>
    </nav>
    <template v-slot:hubContent>
      <div class="hub-te-manager">
        <admin-center-te></admin-center-te>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/HubElement";

export default {
  name: "ank-admin-te-manager",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-te": resolve => {
      import("../TeManager/TeManager.vue").then(Component => {
        resolve(Component.default);
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
