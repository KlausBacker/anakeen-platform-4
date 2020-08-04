<template>
  <div class="incoming-tasks-wrapper">
    <ank-split-panes
      ref="splitter"
      watch-slots
      vertical
      collapse-buttons
      class="tasks-splitter"
      localStorageKey="admin-center-tasks-splitter"
    >
      <div class="tasks-grid-wrapper">
        <div ref="gridIncomingTasks"></div>
      </div>
      <div class="tasks-form-wrapper">
        <div v-show="!displaySMartElement" class="tasks-empty">
          <span class="material-icons">schedule</span>
          <p>{{ $t("AdminCenterTasks.Select an Task") }}</p>
        </div>
        <ank-smart-element
          v-show="displaySMartElement"
          @afterSave="afterSaveRefreshGrid"
          ref="openDocIncomingTask"
          class="incoming-task-element-content"
        />
        <div ref="smartFormAlert"></div>
      </div>
    </ank-split-panes>
  </div>
</template>

<style lang="scss">
@import "./IncomingTasks.scss";
</style>
<script lang="ts" src="./IncomingTasks.ts" />
