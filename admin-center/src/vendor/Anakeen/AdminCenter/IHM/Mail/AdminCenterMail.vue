<template>
  <div class="mail-parent">
    <ank-split-panes
      ref="mailSplitter"
      watch-slots
      vertical
      collapse-buttons
      class="mail-splitter"
      local-storage-key="admin-center-mail-splitter"
    >
      <ank-se-vue-grid
        ref="grid"
        class="mail-grid"
        collection="MAILTEMPLATE"
        controller="DEFAULT_GRID_CONTROLLER"
        :pageable="pageable"
        :contextTitles="false"
        refresh
        :actions="actions"
        :columns="columns"
        @rowActionClick="selectMailTemplate"
        filterable
        defaultExpandable
      />
      <div class="mail-form-wrapper">
        <div v-show="!selectedMail" class="mail-empty">
          <span class="material-icons">email</span>
          <p>{{ $t("AdminCenterMail.Select mail template") }}</p>
        </div>
        <ank-smart-element v-show="selectedMail" class="mail-smart-element" ref="mailSmartElement" />
      </div>
    </ank-split-panes>
  </div>
</template>
<style scoped lang="scss">
@import "./AdminCenterMail.scss";
</style>
<script src="./AdminCenterMail.controller.ts" lang="ts"></script>
