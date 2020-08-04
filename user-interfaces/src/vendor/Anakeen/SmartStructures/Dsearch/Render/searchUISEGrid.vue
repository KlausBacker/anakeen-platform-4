<template>
  <ank-se-grid
    v-if="searchId"
    :collection="searchId"
    :pageable="{ pageSizes: [50, 100, 200], pageSize: 50 }"
    class="search-grid"
    ref="searchGrid"
    controller="REPORT_GRID_CONTROLLER"
    defaultExpandable
    @gridError="onGridError"
    @rowActionClick="onRowClick"
  ></ank-se-grid>
</template>
<script>
import AnkSEGrid from "../../../../../../components/lib/AnkSmartElementGrid.esm";

export default {
  name: "SearchUISEGrid",
  components: {
    "ank-se-grid": AnkSEGrid
  },
  props: ["searchId"],
  methods: {
    onGridError(...args) {
      this.$emit("searchGridError", args);
    },
    /*
     * Emit the row action click to prevent the default action (Open new browser tab).
     * With this method, we display the SE in new AnkSeTab
     */
    onRowClick(gridEvent) {
      this.$emit("searchRowActionClick", gridEvent);
    }
  }
};
</script>
<style lang="scss"></style>
