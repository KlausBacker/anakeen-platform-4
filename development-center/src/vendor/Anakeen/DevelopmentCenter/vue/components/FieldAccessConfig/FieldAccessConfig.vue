<template>
    <div class="fall-config-tree" ref="fallConfigTree">
        <div class="fall-config-toolbar">
            <div class="fall-infos">
                <div class="fall-title"><b>Title: </b> {{fallProperties ? fallProperties.title : ""}}</div>
                <div class="fall-name"><b>Logical Name: </b> {{fallProperties ? fallProperties.name : ""}}</div>
                <div class="fall-id"><b>Id: </b> {{fallProperties ? fallProperties.id : ""}}</div>
            </div>
            <div v-if="detachable" class="detach-button">
                <button @click="onDetachComponent" class="k-button k-button-icon"><i class="k-icon k-i-hyperlink-open"></i></button>
            </div>
        </div>
        <ank-tree-list v-if="treeConfigReady"
                       ref="ankTreeList"
                       class="fall-config-treelist"
                       :url="resolvedUrl"
                       :items="treeColumns"
                       :filterable="false"
                       :resizable="false"
                       :getValues="privateMethods.parseData"
                       :headerTemplate="privateMethods.getHeaderTemplate"
                       :columnTemplate="privateMethods.getCellTemplate"
                       :inlineFilters="false"
                       :model="model"
                       @hook:mounted="privateMethods.customizeTree"
        ></ank-tree-list>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./FieldAccessConfig.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./FieldAccessConfig.controller.js"></script>