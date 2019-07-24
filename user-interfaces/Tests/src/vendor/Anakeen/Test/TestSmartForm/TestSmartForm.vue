<template>
  <div class="test-smart-form">
    <ank-splitter
      ref="smartFormSplitter"
      class="smart-form-splitter"
      :panes="panes"
    >
      <template slot="left">
        <div class="left-pane">
          <div><h1>Configuration</h1></div>
          <v-jsoneditor class="json-editor" v-model="json" :options="options" :plus="false" height="400px" @error="onError"/>
        </div>
      </template>
      <template slot="right">
        <div class="right-pane">
          <div><h1>Smart Form</h1></div>
          <div class="smart-form">
            <ank-smart-form ref="smartFormRef" :config="json" />
          </div>
        </div>
      </template>
    </ank-splitter>
  </div>
</template>
<style lang="scss">
@import "./TestSmartForm.scss";
</style>
<script src="./TestSmartForm.controller.ts" lang="ts"></script>
