<template>
  <ank-se-grid
    :url-config="`/api/v2/smartstructures/dsearch/gridConfig/${searchId}`"
    :page-sizes="[50, 100, 500]"
    :collection="searchId"
    class="dsearch-result-grid"
    ref="gridPreview"
  >
    <template v-slot:gridHeader="{ gridComponent }">
      <ank-se-grid-export-button
        :gridComponent="gridComponent"
        v-show="showProgress"
        @exportDone="hideButton"
        ref="exportButton"
      ></ank-se-grid-export-button>
    </template>
  </ank-se-grid>
</template>
<script>
import AnkSEGrid, { AnkSEGridExportButton } from "../../../../../../components/lib/AnkSEGrid";
export default {
  name: "search-ui-view",
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-se-grid-export-button": AnkSEGridExportButton
  },
  data() {
    return {
      searchId: window.ankGridSearchId.toString(),
      showProgress: false
    };
  },
  methods: {
    hideButton() {
      this.showProgress = false;
    },
    showButton() {
      this.showProgress = true;
    },
    export() {
      this.$refs.exportButton.export();
    }
  }
};
</script>
<style>
.dsearch-result-grid {
  height: 100%;
  width: 100%;
}
</style>
