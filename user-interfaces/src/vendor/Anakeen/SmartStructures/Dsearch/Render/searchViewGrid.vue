<template>
  <ank-se-grid
    ref="gridPreview"
    :collection="searchId"
    :pageable="{ pageSizes: [50, 100, 200], pageSize: 50 }"
    default-expandable
    refresh
    controller="REPORT_GRID_CONTROLLER"
    class="dsearch-result-grid"
    @rowActionClick="onRowClick"
  >
    <template v-slot:gridHeader="{ gridComponent }">
      <ank-se-grid-export-button
        v-show="showProgress"
        ref="exportButton"
        :grid-component="gridComponent"
        direction="left"
        @exportDone="hideButton"
      ></ank-se-grid-export-button>
    </template>
  </ank-se-grid>
</template>
<script>
import AnkSEGrid from "@anakeen/user-interfaces/components/lib/AnkSmartElementGrid.esm";
import AnkSEGridExportButton from "@anakeen/user-interfaces/components/lib/AnkSmartElementGridExportButton.esm";

export default {
  name: "SearchUiView",
  components: {
    "ank-se-grid": AnkSEGrid,
    "ank-se-grid-export-button": AnkSEGridExportButton
  },
  props: {
    searchId: { type: String, default: "" }
  },
  data() {
    return {
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
