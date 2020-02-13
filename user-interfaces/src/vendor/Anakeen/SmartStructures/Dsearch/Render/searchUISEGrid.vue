<template>
  <ank-se-grid
    v-if="searchId"
    :collection="searchId"
    class="search-grid"
    ref="searchGrid"
    expandable
    controller="REPORT_GRID_CONTROLLER"
  >
  </ank-se-grid>
</template>
<script>
import AnkSEGrid from "../../../../../../components/lib/AnkSmartElementVueGrid.esm";

export default {
  name: "SearchUISEGrid",
  components: {
    "ank-se-grid": AnkSEGrid
  },
  props: ["searchId"],
};
</script>
<style lang="scss"></style>
