<template>
  <hub-element-layout>
    <nav>
      <i class="fa fa-info-circle stats-icon" aria-hidden="true"></i>
      <span
        v-if="!isDockCollapsed"
      >{{ $t("AdminCenterAbout.Title About Manager")}}</span>
    </nav>
    <template v-slot:hubContent>
      <div class="statistics-parent">
        <admin-center-statistics
        ></admin-center-statistics>
      </div>
    </template>
  </hub-element-layout>
</template>
<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";

export default {
  name: "ank-admin-infos",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-statistics": () =>
        new Promise(resolve => {
          import("../../Statistics/AdminCenterInfo.vue").then(Component => {
            resolve(Component.default);
          });
        })
  },
  data() {
    return ({});
  }
};
</script>
<style>
.statistics-parent {
  height: 100%;
  width: 100%;
}

.stats-icon {
  font-size: 24px;
  margin-right: 0.5rem;
}
</style>
