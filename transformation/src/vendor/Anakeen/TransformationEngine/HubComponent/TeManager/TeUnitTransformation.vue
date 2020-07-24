<template>
  <div class="te-unit-transformation-parent">
    <div class="te-unit-form">
      <ank-smart-form
        ref="teUnitTransformationFileForm"
        :config="smartFormData"
        @smartElementMounted="smartElementMounted"
      ></ank-smart-form>
    </div>
    <ank-split-panes
      ref="splitter"
      watch-slots
      vertical
      class="te-unit-transformation-splitter"
      local-storage-key="te-unit-transformation-splitter"
    >
      <div v-show="kProgress" class="te-check">
        <div class="te-checkbar">
          <p>{{ kProgress && kProgress.value() }} - {{ progressText }}</p>
          <div ref="progressBar" />
        </div>
        <div class="te-check-log">
          <ol>
            <li v-for="msg in progressMessages">{{ msg }}</li>
          </ol>
        </div>
        <div v-if="fileToDownload" class="te-unit-download">
          <a class="btn btn-primary te-unit-download-btn" :href="fileToDownload" download target="_blank"
            ><i class="fa fa-download" aria-hidden="true"></i> Download generated file</a
          >
        </div>
      </div>
      <div class="te-task-check-info">
        <div v-if="!checkedTask.tid" class="te-empty">
          <span class="k-icon k-i-information splitter-empty-icon splitter-default-empty-icon"></span>
          <p>{{ $t("AdminCenterTransformationFileManager.Splitter empty") }}</p>
        </div>
        <te-task-info v-else :task-data="checkedTask" />
      </div>
    </ank-split-panes>
  </div>
</template>
<style lang="scss">
@import "./TeUnitTransformation.scss";
</style>
<script src="./TeUnitTransformation.controller.ts" lang="ts" />
