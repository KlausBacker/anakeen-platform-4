<template>
  <ank-se-grid
    v-if="searchId"
    :collection="searchId"
    :pageable="{ pageSizes: [50, 100, 200], pageSize: 50 }"
    class="search-grid"
    ref="searchGrid"
    controller="REPORT_GRID_CONTROLLER"
    defaultExpandable
  >
  </ank-se-grid>
</template>
<script>
import AnkSEGrid from "../../../../../../components/lib/AnkSmartElementGrid.esm";

export default {
  name: "SearchUISEGrid",
  components: {
    "ank-se-grid": AnkSEGrid
  },
  props: ["searchId"]
};
</script>
<style lang="scss"></style>
