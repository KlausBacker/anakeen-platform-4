<template>
  <hub-element-layout>
    <nav>
      <i class="fa fa-bar-chart stats-icon" aria-hidden="true"></i>
      <span
        v-if="!isDockCollapsed"
      >{{ $t("AdminCenterStatistics.Title Statistics Manager")}}</span>
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
  name: "ank-admin-statistics",
  extends: HubElement, // ou mixins: [ HubElementMixins ],
  components: {
    "admin-center-statistics": () =>
        new Promise(resolve => {
          import("../../Statistics/AdminCenterStatistics.vue").then(Component => {
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
