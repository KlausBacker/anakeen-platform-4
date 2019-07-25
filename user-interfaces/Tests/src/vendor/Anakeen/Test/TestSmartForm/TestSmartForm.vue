<template>
  <div class="test-smart-form">
    <ank-split-panes watch-slots vertical>
        <div class="pane left-pane">
          <header><h1>Configuration </h1></header>
          <v-jsoneditor class="json-editor" v-model="json" :options="options" :plus="false" height="400px" @error="onError"/>
        </div>
        <div class="pane right-pane">
          <header><h1>Smart Form</h1></header>
          <div class="smart-form">
            <ank-smart-form ref="smartFormRef" :config="json" />
          </div>
        </div>
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./TestSmartForm.scss";
</style>
<script src="./TestSmartForm.controller.ts" lang="ts"></script>
