<template>
  <div class="workflow-parent">
    <wfl-list
      ref="wflSSList"
      position="left"
      :filter="{ placeholder: 'Search a workflow' }"
      v-model="selectedWfl"
    >
    </wfl-list>
    <div class="workflow-content">
      <div class="wfl-content__empty" v-if="isEmpty">
        <span class="k-icon k-i-folder-open wfl-content__empty-icon"></span>
        <span class="wfl-content__empty-text">Select a workflow</span>
      </div>
      <ank-splitter
        v-else
        class="workflow-splitter"
        :panes="panes"
        localStorageKey="admin-center-workflow-splitter"
        ref="wflSplitter"
      >
        <template slot="left">
          <img class="wfl-graph" :src="getGraphUrl"></img>
        </template>
        <template slot="right">
          <wfl-data :wflName="selectedWfl" @EditStepColorSuccess="force=Math.random()"></wfl-data>
        </template>
      </ank-splitter>
    </div>
  </div>
</template>
<script src="./AdminCenterWorkflow.controller.ts" lang="ts"></script>
<style lang="scss" scoped>
@import "./AdminCenterWorkflow.scss";
</style>
