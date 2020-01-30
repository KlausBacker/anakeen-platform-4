<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">code</i>
      <span v-if="!isDockCollapsed">Smart Structure Manager</span>
    </nav>
    <template v-slot:hubContent>
      <div class="structure-parent">
        <admin-center-structure v-model="selectedSS" @structure-selected="newSelectedSS"></admin-center-structure>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";

export default {
  name: "ank-admin-structure",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-structure": () => import("../../SmartStructureManager/AdminCenterStructure.vue")
  },
  watch: {
    selectedSS(newValue) {
      this.navigate(this.routeUrl() + "/" + newValue);
    },
  },
  created() {
    this.subRouting();
  },
  data() {
    const that = this;
    return {
      selectedSS: "",
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      subRouting: () => {
        const url = (this.routeUrl() + "/:structureId").replace(/\/\/+/g, "/");

        this.registerRoute(url, params => {
          this.selectedSS = params.structureId;
        }).resolve(window.location.pathname);
      },
      newSelectedSS(newValue) {
        that.selectedSS = newValue;
      }
    };
  }
};
</script>
<style>
.structure-parent {
  height: 100%;
  width: 100%;
}
</style>
