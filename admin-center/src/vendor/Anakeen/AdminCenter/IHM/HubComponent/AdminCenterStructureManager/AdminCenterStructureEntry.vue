<template>
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">code</i>
      <span
        v-if="!isDockCollapsed"
      >{{ $t("AdminCenterSmartStructure.Title Smart Structure Manager")}}</span>
    </nav>
    <template v-slot:hubContent>
      <div class="structure-parent">
        <admin-center-structure
          v-model="selectedSS"
          @structureSelected="newSelectedSS"
          @tabChange="newSelectedTab"
          :tabFromUrl="selectedTab"
        ></admin-center-structure>
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
      if (this.selectedTab === "") {
        this.selectedTab = "informations";
      }
      this.navigate(this.routeUrl() + "/" + newValue + "/" + this.selectedTab);
    },
    selectedTab(newValue) {
      this.navigate(this.routeUrl() + "/" + this.selectedSS + "/" + newValue);
    }
  },
  created() {
    this.subRouting();
  },
  data() {
    const that = this;
    return {
      actualUrl: "",
      selectedSS: "",
      selectedTab: "",
      routeUrl: () => {
        return this.entryOptions.completeRoute;
      },
      subRouting: () => {
        const url = (this.routeUrl() + "/:structureId/:tab").replace(/\/\/+/g, "/");
        this.registerRoute(url, params => {
          this.selectedSS = params.structureId;
          this.selectedTab = params.tab;
        }).resolve(window.location.pathname);
      },
      newSelectedSS(newValue) {
        that.selectedSS = newValue;
      },
      newSelectedTab(newValue) {
        that.selectedTab = newValue;
      },
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
