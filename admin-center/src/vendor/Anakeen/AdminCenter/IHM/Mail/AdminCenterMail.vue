<template>
  <div class="mail-parent">
    <ank-split-panes
      ref="mailSplitter"
      watch-slots
      vertical
      class="mail-splitter"
      local-storage-key="admin-center-mail-splitter"
    >
      <!--      <ank-se-grid-->
      <!--        class="mail-grid"-->
      <!--        :pageSizes="[50, 100, 200]"-->
      <!--        ref="grid"-->
      <!--        collection="MAILTEMPLATE"-->
      <!--        :contextTitles="false"-->
      <!--        filterable="inline"-->
      <!--        @action-click="selectMailTemplate"-->
      <!--      >-->

      <!--        <ank-se-grid-column title="Identification" field="id" hidden="true"></ank-se-grid-column>-->
      <!--        <ank-se-grid-column title="Title" field="tmail_title"></ank-se-grid-column>-->
      <!--        <ank-se-grid-column title="Subject" field="tmail_subject"></ank-se-grid-column>-->
      <!--        <ank-se-grid-actions>-->
      <!--          <ank-se-grid-action action="consultMailTemplate" title="Display"></ank-se-grid-action>-->
      <!--        </ank-se-grid-actions>-->
      <!--      </ank-se-grid>-->
      <ank-se-vue-grid
        ref="grid"
        class="mail-grid"
        collection="MAILTEMPLATE"
        controller="DEFAULT_GRID_CONTROLLER"
        :actions="actions"
        :columns="columns"
        @rowActionClick="selectMailTemplate"
        filterable
        defaultExpandable
      />
      <div class="mail-form-wrapper">
        <div v-show="!selectedMail" class="mail-empty">
          <span class="material-icons">email</span>
          <p>Select mail template</p>
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
