<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">find_replace</i>
      <span v-if="!isDockCollapsed">Smart Criteria</span>
    </nav>
    <template v-slot:hubContent>
      <div :class="{ warning: hasWarning, error: hasError, 'test-smart-criteria': true }">
        <ank-pane-splitter watch-slots vertical>
          <div splitpanes-size="10" class="pane example-pane">
            <smart-criteria-examples ref="smartExampleRef" @select="setConfig"/>
          </div>
          <div splitpanes-size="45" class="criteria-section criteria-left">
            <header :title="tooltip"><h1>Configuration </h1><button class="btn btn-primary" @click="recordNewExample"><i class="fa fa-plus fa-2x"/></button></header>
            <v-json-editor
              ref="jsonEditorRef"
              class="json-editor"
              v-model="criteriaConfig"
              :options="options"
              :plus="false"
              @error="onError"
            />
          </div>
          <div splitpanes-size="45" class="criteria-section criteria-right">
            <header :title="tooltip"><h1>Smart Criteria</h1></header>
            <ank-smart-criteria :config="criteriaConfig" :submit="true" @hook:mounted="initCriteriaExample" @smartCriteriaSubmitClick="onSubmitButtonClick" @smartCriteriaReady="testSmartCriteriaReady" @smartCriteriaChange="testSmartCriteriaChange" @smartCriteriaError="testSmartCriteriaError" ref="smartCriteria"/>
            <ank-smart-grid collection="DEVBILL" ref="smartGrid" :smartCriteriaValue="smartFilter" :columns="gridColumns"/>
          </div>
        </ank-pane-splitter>
      </div>
    </template>
  </hub-element-layout>
</template>
<style lang="scss">
  @import "TestSmartCriteriaEntry";
</style>
<script lang="js" src="./TestSmartCriteriaEntry.controller.js"></script>
