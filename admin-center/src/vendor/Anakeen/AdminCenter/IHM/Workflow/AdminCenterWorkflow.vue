<template>
  <div class="workflow-parent">
    <wfl-list
      ref="wflSSList"
      position="left"
      :filter="{ placeholder: $t('AdminCenterWorkflow.Search a workflow') }"
      v-model="selectedWfl"
    >
    </wfl-list>
    <div class="workflow-content">
      <div class="wfl-content__empty" v-if="isEmpty">
        <span class="k-icon k-i-folder-open wfl-content__empty-icon"></span>
        <span class="wfl-content__empty-text">{{ $t("AdminCenterWorkflow.Select a workflow") }}</span>
      </div>
      <ank-split-panes watch-slots vertical collapse-buttons
        v-else
        class="workflow-splitter"
        localStorageKey="admin-center-workflow-splitter"
        ref="wflSplitter">

          <img class="wfl-graph" :src="getGraphUrl"></img>

          <wfl-data :wflName="selectedWfl" @EditStepColorSuccess="force=Math.random()"></wfl-data>

      </ank-split-panes>
    </div>
  </div>
</template>
<script src="./AdminCenterWorkflow.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./AdminCenterWorkflow.scss";
</style>
