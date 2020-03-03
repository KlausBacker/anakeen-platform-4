<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">table_chart</i>
      <span v-if="!isDockCollapsed">Smart Grid</span>
    </nav>
    <template v-slot:hubContent>
      <div :class="{ warning: hasWarning, error: hasError, 'test-smart-grid': true }">
        <ank-pane-splitter watch-slots vertical>
          <div splitpanes-size="33" class="grid-section grid-left">
            <header :title="tooltip"><h1>Configuration</h1></header>
            <v-json-editor
              ref="jsonEditorRef"
              class="json-editor"
              v-model="gridConfig"
              :options="options"
              :plus="false"
              @error="onError"
            />
          </div>
          <div splitpanes-size="67" class="grid-section grid-right">
            <header :title="tooltip"><h1>Smart Grid</h1></header>
            <ank-smart-element-grid @rowActionClick="onRowActionClick" v-bind="gridConfig"> </ank-smart-element-grid>
          </div>
        </ank-pane-splitter>
      </div>
    </template>
  </hub-element-layout>
</template>
<style lang="scss">
  @import "./TestSmartGridEntry.scss";
</style>
<script lang="js" src="./TestSmartGridEntry.controller.js"></script>
