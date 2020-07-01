<template>
  <div class="previous-tasks-wrapper">
    <ank-split-panes
      ref="splitter"
      watch-slots
      vertical
      class="previous-tasks-splitter"
      localStorageKey="admin-center-tasks-splitter"
    >
      <div class="previous-tasks-grid-wrapper">
        <div ref="gridPreviousTask"></div>
      </div>
      <div class="previous-tasks-form-wrapper">
        <div v-show="!displaySMartElement" class="previous-tasks-empty">
          <span class="material-icons">schedule</span>
          <p>{{ $t("AdminCenterTasks.Select an Task") }}</p>
        </div>
        <ank-smart-element
          v-show="displaySMartElement"
          @afterSave="afterSaveRefreshGrid"
          ref="openDocPreviousTask"
          class="incoming-task-element-content"
        />
        <div ref="smartFormAlert"></div>
      </div>
    </ank-split-panes>
  </div>
</template>

<style lang="scss">
@import "./PreviousTasks.scss";
</style>
<script lang="ts" src="./PreviousTasks.ts" />
