<template>
    <button @click="onClickRefresh" class="dev-center-refresh-button k-button k-button-icontext" title="Refresh Data">
        <slot>
            <i class="dev-center-refresh-button-icon k-icon k-i-refresh"></i>
            <span class="dev-center-refresh-button-icon k-label">Refresh Data</span>
        </slot>
    </button>
</template>

<script>
    import { REFRESH_EVENT_KEY } from "./globalMixins";
  export default {
    name: "RefreshButton",
    props: {

    },
    methods: {
      onClickRefresh() {
        if (this.$_busEvent) {
          this.$_busEvent.$emit(REFRESH_EVENT_KEY)
        }
      }
    }
  }
</script>

<style scoped>

</style>