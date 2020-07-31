<template>
  <div class="rd-parent">
    <ank-split-panes
      ref="rdSplitter"
      watch-slots
      vertical
      class="rd-splitter"
      local-storage-key="admin-center-rd-splitter"
    >
      <ank-se-vue-grid
        ref="grid"
        class="rd-grid"
        collection="RENDERDESCRIPTION"
        controller="DEFAULT_GRID_CONTROLLER"
        :pageable="pageable"
        refresh
        :contextTitles="false"
        :actions="actions"
        :columns="columns"
        @rowActionClick="selectRenderDescription"
        filterable
        defaultExpandable
      />
      <div class="rd-form-wrapper">
        <div v-show="!selectedRenderDescription" class="rd-empty">
          <span class="material-icons">info</span>
          <p>{{ $t("AdminCenterRenderDescription.Select render description") }}</p>
        </div>
        <ank-smart-element v-show="selectedRenderDescription" class="rd-smart-element" ref="rdSmartElement" />
      </div>
    </ank-split-panes>
  </div>
</template>
<style scoped lang="scss">
@import "./AdminCenterRenderDescription.scss";
</style>
<script src="./AdminCenterRenderDescription.controller.ts" lang="ts"></script>
