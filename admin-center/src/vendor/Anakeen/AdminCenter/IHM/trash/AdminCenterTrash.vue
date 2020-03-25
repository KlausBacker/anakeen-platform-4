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
        controller="TRASH_GRID_CONTROLLER"
        :sortable="false"
        pageable
        filterable
        default-expandable
        @rowActionClick="selectTrash"
        @dataBound="onGridDataBound"
      />
      <div class="trash-se-parent">
        <div v-if="!selectedTrashBool" class="trash-empty">
          <span class="material-icons">delete</span>
          <p>Select Smart Element</p>
        </div>
        <ank-smart-element ref="trashSmartElement" class="trash-smart-element" />
      </div>
    </ank-split-panes>
  </div>
</template>

<style scoped lang="scss">
@import "./AdminCenterTrash.scss";
</style>
<script src="./AdminCenterTrash.controller.ts" lang="ts"></script>
