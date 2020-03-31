<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">list</i>
      <span v-if="!isDockCollapsed">Smart List</span>
    </nav>
    <template v-slot:hubContent>
      <div :class="{ warning: hasWarning, error: hasError, 'test-smart-list': true }">
        <ank-pane-splitter watch-slots vertical>
          <div splitpanes-size="33" class="list-section list-left">
            <header :title="tooltip"><h1>Configuration</h1></header>
            <v-json-editor
              ref="jsonEditorRef"
              class="json-editor"
              v-model="listConfig"
              :options="options"
              :plus="false"
              @error="onError"
            />
          </div>
          <div splitpanes-size="67" class="list-section list-right">
            <header :title="tooltip"><h1>Smart List</h1></header>
            <ank-smart-element-list v-model="selected" v-bind="listConfig"> </ank-smart-element-list>
          </div>
        </ank-pane-splitter>
      </div>
    </template>
  </hub-element-layout>
</template>
<style lang="scss">
  @import "./TestSmartListEntry.scss";
</style>
<script lang="js" src="./TestSmartListEntry.controller.js"></script>
