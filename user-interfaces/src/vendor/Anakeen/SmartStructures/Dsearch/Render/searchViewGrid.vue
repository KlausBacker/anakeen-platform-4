<template>
  <ank-se-grid
    :collection="searchId"
    :pageable="{ pageSizes: [50, 100, 200], pageSize: 50 }"
    defaultExpandable
    controller="REPORT_GRID_CONTROLLER"
    class="dsearch-result-grid"
    ref="gridPreview"
    @rowActionClick="onRowClick"
  >
    <template v-slot:gridHeader="{ gridComponent }">
      <ank-se-grid-export-button
        :gridComponent="gridComponent"
        v-show="showProgress"
        @exportDone="hideButton"
        direction="left"
        ref="exportButton"
      ></ank-se-grid-export-button>
    </template>
  </ank-se-grid>
</template>
<script>
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import AnkSEGridExportButton from "@anakeen/user-interfaces/components/lib/AnkSmartElementGridExportButton.esm";

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
<style>
.dsearch-result-grid {
  height: 100%;
  width: 100%;
}
</style>
