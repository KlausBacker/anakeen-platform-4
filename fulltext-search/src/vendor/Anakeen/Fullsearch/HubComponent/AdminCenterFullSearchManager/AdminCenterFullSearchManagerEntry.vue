<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">search</i> <span v-if="!isDockCollapsed"> Fullsearch Manager</span>
    </nav>
    <template v-slot:hubContent>
      <div class="hub-fullsearch-manager">
        <admin-center-fullsearch></admin-center-fullsearch>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";

export default {
  name: "ank-admin-fullsearch-manager",
  extends: HubElement,
  components: {
    "admin-center-fullsearch": () => import("../FullsearchManager/Manager/FullsearchManager.vue")
    }
};
</script>
<style>
.hub-fullsearch-manager {
  height: 100%;
}
</style>
