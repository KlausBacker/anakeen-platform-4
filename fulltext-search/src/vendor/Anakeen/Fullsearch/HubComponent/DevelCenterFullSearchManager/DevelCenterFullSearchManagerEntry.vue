<template>
  <hub-element-layout>
    <nav>
     <span > Fullsearch</span>
    </nav>
    <template v-slot:hubContent>
      <div class="hub-fullsearch-manager">
        <devel-center-fullsearch></devel-center-fullsearch>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";

export default {
  name: "ank-devel-fullsearch-manager",
  extends: HubElement,
  components: {
    "devel-center-fullsearch": () => import("../FullsearchManager/Devel/FullsearchDevel.vue")
    }
};
</script>
<style>
.hub-fullsearch-manager {
  height: 100%;
}
</style>
