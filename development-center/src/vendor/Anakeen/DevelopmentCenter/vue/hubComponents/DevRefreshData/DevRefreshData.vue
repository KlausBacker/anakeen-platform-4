<template>
  <button
    class="dev-center-refresh-button k-button k-button-icontext k-primary"
    title="Refresh Data"
    @click="onClickRefresh"
  >
    <slot>
      <i class="dev-center-refresh-button-icon k-icon k-i-refresh"></i>
      <span class="dev-center-refresh-button-icon k-label">Refresh Data</span>
    </slot>
  </button>
</template>

<script>
import HubElement from "@anakeen/hub-components/components/lib/AnkHubElement.esm";
export default {
  name: "AnkDevRefreshData",
  extends: HubElement,
  methods: {
    onClickRefresh() {
      this.getStore().dispatch("refreshData", {
        root: this.$root
      });
    }
  }
};
</script>

<style scoped></style>
