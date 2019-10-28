<template>
  <div class="trash-parent">
    <div ref="confirm" class="window-confirm" style="display: none"></div>

    <ank-split-panes
      watch-slots
      vertical
      ref="trashSplitter"
      class="trash-splitter"
      localStorageKey="admin-center-trash-splitter"
    >
      <ank-se-grid
        class="trash-grid"
        :sortable="false"
        :refresh="true"
        :pageSizes="[50, 100, 200]"
        ref="grid"
        urlConfig="/api/v2/admin/trash/config/"
        :contextTitles="false"
        @action-click="selectTrash"
        @grid-data-bound="onGridDataBound"
      ></ank-se-grid>

      <div v-if="!selectedTrash" class="trash-empty">
        <span class="material-icons">delete</span>
        <p>Select Smart Element</p>
      </div>

      <ank-smart-element v-else class="trash-smart-element" ref="trashSmartElement"></ank-smart-element>
    </ank-split-panes>
  </div>
</template>


<style scoped lang="scss">
@import "./AdminCenterTrash.scss";
</style>
<script src="./AdminCenterTrash.controller.ts" lang="ts"></script>
