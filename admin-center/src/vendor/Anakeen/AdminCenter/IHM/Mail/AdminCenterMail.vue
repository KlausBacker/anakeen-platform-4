<template>
  <div class="mail-parent">
    <ank-splitter
      ref="mailSplitter"
      class="mail-splitter"
      :panes="panes"
      localStorageKey="admin-center-mail-splitter"
    >
      <template slot="left">
        <ank-se-grid
          class="mail-grid"
          :pageSizes="[50, 100, 200]"
          ref="grid"
          collection="MAILTEMPLATE"
          :contextTitles="false"
          filterable="inline"
          @action-click="selectMailTemplate"
        >
          <ank-se-grid-column
            title="Identification"
            field="id"
            hidden="true"
          ></ank-se-grid-column>
          <ank-se-grid-column
            title="Title"
            field="tmail_title"
          ></ank-se-grid-column>
          <ank-se-grid-column
            title="Subject"
            field="tmail_subject"
          ></ank-se-grid-column>
          <ank-se-grid-actions>
            <ank-se-grid-action
              action="consultMailTemplate"
              title="Display"
            ></ank-se-grid-action>
          </ank-se-grid-actions>
        </ank-se-grid>
      </template>
      <template slot="right">
        <ank-smart-element
          class="mail-smart-element"
          ref="mailSmartElement"
        ></ank-smart-element>
      </template>
    </ank-splitter>
  </div>
</template>
<style scoped lang="scss">
@import "./AdminCenterMail.scss";
</style>
<script src="./AdminCenterMail.controller.ts" lang="ts"></script>
