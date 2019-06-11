<template>
    <button v-if="isDockCollapsed || isDockExpanded" @click="onClickRefresh" class="dev-center-refresh-button k-button k-button-icontext k-primary" title="Refresh Data">
        <slot>
            <i class="dev-center-refresh-button-icon k-icon k-i-refresh"></i>
            <span class="dev-center-refresh-button-icon k-label">Refresh Data</span>
        </slot>
    </button>
</template>

<script>
  import HubElement from "@anakeen/hub-components/components/lib/HubElement";
  export default {
    name: "ank-dev-refresh-data",
    extends: HubElement,
    methods: {
      onClickRefresh() {
        this.getStore().dispatch("refreshData", {
          root: this.$root
        });
      }
    }
  }
</script>

<style scoped>

</style>