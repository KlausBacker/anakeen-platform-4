<template>
    <ank-se-grid url-config="/api/v2/smartstructures/dsearch/gridConfig/<collection>"
                 :collection="searchId" class="dsearch-result-grid" ref="gridPreview">
    </ank-se-grid>
</template>
<script>
    import AnkSEGrid from "../../../../../../components/lib/AnkSEGrid";
    export default {
      name: "search-ui-view",
      components: {
        "ank-se-grid": AnkSEGrid
      },
      data() {
        return {
          searchId: window.ankGridSearchId.toString()
        };
      }
    }
</script>
<style>
    .dsearch-result-grid {
        height: 100%;
        width: 100%;
    }
</style>