<template>
    <div class="workflow-rights-tree">
        <div class="workflow-rights-toolbar">
            <div v-if="visualizeGraph" class="visualize-graph">
                <button @click="onVisualizeGraph" class="k-button k-button-icontext"><i class="k-icon k-i-connector"></i>Graph</button>
            </div>
            <div class="show-all-switch switch-container" style="margin-left: 1rem">
                <label class="switch">
                    <input type="checkbox" @change="privateMethods.onSwitchDisplay">
                    <span class="slider round"></span>
                </label>
                <label class="switch-label">
                    <span>Show labels</span>
                </label>
            </div>
            <div class="graph-infos">
                <div class="graph-title"><b>Title: </b> {{graphProperties ? graphProperties.title : ""}}</div>
                <div class="graph-name"><b>Logical Name: </b> {{graphProperties ? graphProperties.name : ""}}</div>
                <div class="graph-id"><b>Id: </b> {{graphProperties ? graphProperties.id : ""}}</div>
            </div>
            <div v-if="detachable" class="detach-button">
                <button @click="onDetachComponent" class="k-button k-button-icon"><i class="k-icon k-i-hyperlink-open"></i></button>
            </div>
        </div>
        <ank-tree-list v-if="treeConfigReady"
                       ref="ankTreeList"
                       class="workflow-rights-treelist"
                       :url="resolvedWorkflowContent"
                       :items="treeColumns"
                       :filterable="false"
                       :resizable="false"
                       :getValues="privateMethods.parseData"
                       :headerTemplate="privateMethods.getHeaderTemplate"
                       :columnTemplate="privateMethods.getCellTemplate"
                       :inlineFilters="false"
                       :model="model"></ank-tree-list>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./WorkflowRights.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./WorkflowRights.controller.js"></script>