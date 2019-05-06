<template>
    <ank-se-grid v-if="searchId" url-config="/api/v2/smartstructures/dsearch/gridConfig/<collection>"
                 :server-paging="true"
                 :server-sorting="true" :server-filtering="true" :collection="searchId" class="search-grid"
                 :contextTitles="false" ref="searchGrid">
    </ank-se-grid>
</template>
<script>
  import AnkSEGrid from "../../../../../../components/lib/AnkSEGrid";

  export default {
    name: "SearchUISEGrid",
    components: {
      "ank-se-grid": AnkSEGrid
    },
    created() {
      const that = this;
      window.dcp.document.documentController(
        "addEventListener",
        "custom:content",
        {
          name: "getTmpSearchId",
          documentCheck: function isDsearch(document) {
            return document.type === "search";
          }
        },
        function prepareResultEditEvents(event, data) {
          that.searchId = data.id.toString();
          if (that.$refs.searchGrid) {
            that.$refs.searchGrid.privateScope.initGrid();
          }
        });
      window.dcp.document.documentController(
        "addEventListener",
        "custom:content:view",
        {
          name: "getTmpViewId",
          documentCheck: function isReport(document) {
            return document.renderMode === "view" && document.type === "search";
          }
        },
        function prepareResultViewEvents(event, data) {
          that.searchId = data.id.toString();
          if (that.$refs.searchGrid) {
            that.$refs.searchGrid.privateScope.initGrid();
          }
        });
    },
    data() {
      return {
        searchId: null
      };
    }
  };
</script>
<style lang="scss">
</style>