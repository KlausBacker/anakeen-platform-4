<!--suppress ALL -->
<template>
  <div class="test-smart-form" v-bind:class="{ warning: hasWarning, error: hasError }">
    <ank-split-panes watch-slots vertical>
        <div splitpanes-size="10" class="pane example-pane">
            <smart-form-examples ref="smartExampleRef" @select="setJson"/>
        </div>
        <div splitpanes-size="45" class="pane left-pane">
          <header :title="tooltip"><h1>Configuration </h1><button class="btn btn-primary" @click="recordNewExample"><i class="fa fa-plus fa-2x"/></button></header>
          <v-jsoneditor ref="jsonEditorRef" class="json-editor" v-model="json" :options="options" :plus="false"  @error="onError"/>
        </div>
        <div splitpanes-size="45" class="pane right-pane">
          <header><h1>Smart Form</h1></header>
          <div class="smart-form">
            <ank-smart-form ref="smartFormRef" :config="json" :options="{force:true}" />
          </div>
        </div>
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./TestSmartForm.scss";
</style>
<script src="./TestSmartForm.controller.ts" lang="ts"></script>
