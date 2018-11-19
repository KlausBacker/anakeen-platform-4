<template>
    <div class="workflow-rights-tree">
        <ank-tree-list v-if="treeConfigReady"
                       ref="ankTreeList"
                       :url="resolvedWorkflowContent"
                       :items="treeColumns"
                       :filterable="false"
                       :getValues="privateMethods.parseData"
                       :headerTemplate="privateMethods.getHeaderTemplate"
                       :columnTemplate="privateMethods.getCellTemplate"
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