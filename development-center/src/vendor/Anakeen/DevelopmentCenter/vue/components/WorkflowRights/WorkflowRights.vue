<template>
  <div class="workflow-rights-tree">
    <div class="workflow-rights-toolbar">
      <div v-if="visualizeGraph" class="visualize-graph">
        <button class="k-button k-button-icontext" @click="onVisualizeGraph">
          <i class="k-icon k-i-connector"></i>Graph
        </button>
      </div>
      <div class="show-all-switch switch-container" style="margin-left: 1rem">
        <label class="switch">
          <input type="checkbox" @change="privateMethods.onSwitchDisplay" />
          <span class="slider round"></span>
        </label>
        <label class="switch-label">
          <span>Show labels</span>
        </label>
      </div>
      <div class="graph-infos">
        <div class="graph-title"><b>Title: </b> {{ graphProperties ? graphProperties.title : "" }}</div>
        <div class="graph-name"><b>Logical Name: </b> {{ graphProperties ? graphProperties.name : "" }}</div>
        <div class="graph-id"><b>Id: </b> {{ graphProperties ? graphProperties.id : "" }}</div>
      </div>
      <div v-if="detachable" class="detach-button">
        <button class="k-button k-button-icon" @click="onDetachComponent">
          <i class="k-icon k-i-hyperlink-open"></i>
        </button>
      </div>
    </div>
    <ank-tree-list
      v-if="treeConfigReady"
      ref="ankTreeList"
      class="workflow-rights-treelist"
      :url="resolvedWorkflowContent"
      :items="treeColumns"
      :filterable="false"
      :resizable="false"
      :get-values="privateMethods.parseData"
      :header-template="privateMethods.getHeaderTemplate"
      :column-template="privateMethods.getCellTemplate"
      :inline-filters="false"
      :model="model"
      :sort="sortOptions"
      messages="There are no rights for this workflow"
    ></ank-tree-list>
  </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
@import "./WorkflowRights.scss";
</style>
<!-- Global CSS -->
<style lang="scss"></style>
<script src="./WorkflowRights.controller.js"></script>
