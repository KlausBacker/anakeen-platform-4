<template>
  <ank-se-grid
    v-if="searchId"
    :url-config="`/api/v2/smartstructures/dsearch/gridConfig/${searchId}`"
    :server-paging="true"
    :page-sizes="[50, 100, 500]"
    :server-sorting="true"
    :server-filtering="true"
    :collection="searchId"
    class="search-grid"
    :contextTitles="false"
    ref="searchGrid"
  >
  </ank-se-grid>
</template>
<script>
import AnkSEGrid from "../../../../../../components/lib/AnkSEGrid";

export default {
  name: "SearchUISEGrid",
  components: {
    "ank-se-grid": AnkSEGrid
  },
  props: ["searchId"]
};
</script>
<style lang="scss"></style>
