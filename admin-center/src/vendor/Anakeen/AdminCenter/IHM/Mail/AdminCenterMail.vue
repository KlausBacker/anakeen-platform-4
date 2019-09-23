<template>
  <div class="mail-parent">
    <ank-split-panes
      watch-slots
      vertical
      ref="mailSplitter"
      class="mail-splitter"
      localStorageKey="admin-center-mail-splitter"
    >
      <ank-se-grid
        class="mail-grid"
        :pageSizes="[50, 100, 200]"
        ref="grid"
        collection="MAILTEMPLATE"
        :contextTitles="false"
        filterable="inline"
        @action-click="selectMailTemplate"
      >
        <ank-se-grid-column title="Identification" field="id" hidden="true"></ank-se-grid-column>
        <ank-se-grid-column title="Title" field="tmail_title"></ank-se-grid-column>
        <ank-se-grid-column title="Subject" field="tmail_subject"></ank-se-grid-column>
        <ank-se-grid-actions>
          <ank-se-grid-action action="consultMailTemplate" title="Display"></ank-se-grid-action>
        </ank-se-grid-actions>
      </ank-se-grid>
      <ank-smart-element class="mail-smart-element" ref="mailSmartElement"></ank-smart-element>
    </ank-split-panes>
  </div>
</template>
<style scoped lang="scss">
@import "./AdminCenterMail.scss";
</style>
<script src="./AdminCenterMail.controller.ts" lang="ts"></script>
