<template>
  <div class="trash-parent">
    <div ref="confirm" class="window-confirm" style="display: none;" />
    <ank-split-panes
      ref="trashSplitter"
      watch-slots
      vertical
      class="trash-splitter"
      local-storage-key="admin-center-trash-splitter"
    >
      <ank-se-grid
        ref="grid"
        class="trash-grid"
        :sortable="false"
        :refresh="true"
        :page-sizes="[50, 100, 200]"
        url-config="/api/v2/admin/trash/config/"
        :context-titles="false"
        @action-click="selectTrash"
        @grid-data-bound="onGridDataBound"
      />
      <div class="trash-se-parent">
        <div v-if="!selectedTrash" class="trash-empty">
          <span class="material-icons">delete</span>
          <p>Select Smart Element</p>
        </div>
        <ank-smart-element class="trash-smart-element" ref="trashSmartElement" />
      </div>
    </ank-split-panes>
  </div>
</template>

<style scoped lang="scss">
@import "./AdminCenterTrash.scss";
</style>
<script src="./AdminCenterTrash.controller.ts" lang="ts"></script>
