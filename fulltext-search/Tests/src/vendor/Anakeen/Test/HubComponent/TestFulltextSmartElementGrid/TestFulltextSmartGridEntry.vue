<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout class="test-smart-grid-fulltext-hub-entry">
    <nav>
      <span class="hub-icon test-smart-grid-fulltext-icon">
        <i class="material-icons inner">table_chart</i>
        <i class="material-icons outer">search</i>
      </span>
      <span class="test-smart-grid-fulltext-label" v-if="!isDockCollapsed">Smart Grid Fulltext</span>
    </nav>
    <template v-slot:hubContent>
      <div :class="{ warning: hasWarning, error: hasError, 'test-smart-grid-fulltext': true }">
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
            <ank-smart-element-grid v-bind="gridConfig"> </ank-smart-element-grid>
          </div>
        </ank-pane-splitter>
      </div>
    </template>
  </hub-element-layout>
</template>
<style lang="scss">
  @import "./TestFulltextSmartGridEntry.scss";
</style>
<script lang="js" src="./TestFulltextSmartGridEntry.controller.js"></script>
