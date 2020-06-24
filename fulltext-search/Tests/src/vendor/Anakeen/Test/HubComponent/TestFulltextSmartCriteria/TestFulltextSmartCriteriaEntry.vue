<template xmlns:v-slot="http://www.w3.org/1999/XSL/Transform">
  <hub-element-layout>
    <nav>
      <i class="material-icons hub-icon">find_replace</i>
      <span v-if="!isDockCollapsed">Smart Criteria Fulltext</span>
    </nav>
    <template v-slot:hubContent>
      <div :class="{ warning: hasWarning, error: hasError, 'test-smart-criteria': true }">
        <ank-pane-splitter watch-slots vertical>
          <div splitpanes-size="10" class="pane example-pane">
            <smart-criteria-examples ref="smartExampleRef" @select="setConfig" />
          </div>
          <div splitpanes-size="45" class="criteria-section criteria-left">
            <header :title="tooltip">
              <h1>Configuration</h1>
              <button class="btn btn-primary" @click="recordNewExample"><i class="fa fa-plus fa-2x" /></button>
            </header>
            <v-json-editor
              ref="jsonEditorRef"
              v-model="criteriaConfig"
              class="json-editor"
              :options="options"
              :plus="false"
              @error="onError"
            />
          </div>
          <div splitpanes-size="45" class="criteria-section criteria-right">
            <header :title="tooltip"><h1>Smart Criteria</h1></header>
            <ank-fulltext-smart-criteria
              ref="smartCriteria"
              :config="criteriaConfig"
              :responsive-columns="responsiveColumns"
              :submit="true"
              @hook:mounted="initCriteriaExample"
              @smartCriteriaValidated="onCriteriaValidated"
              @smartCriteriaReady="testSmartCriteriaReady"
              @smartCriteriaChange="testSmartCriteriaChange"
              @smartCriteriaError="testSmartCriteriaError"
            />
            <ank-smart-grid
              ref="smartGrid"
              collection="DEVBILL"
              :smart-criteria-value="smartFilter"
              :columns="gridColumns"
            />
          </div>
        </ank-pane-splitter>
      </div>
    </template>
  </hub-element-layout>
</template>
<style lang="scss">
@import "TestFulltextSmartCriteriaEntry";
</style>
<script lang="js" src="./TestFulltextSmartCriteriaEntry.controller.js"></script>
