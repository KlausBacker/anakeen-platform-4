<template>
    <div class="smart-elements-section">
        <kendo-splitter ref="splitter" orientation="horizontal" style="height:100%" :panes="panes">
            <div>
                <ank-se-grid class="se-grid" :pageSizes="[100, 200, 500]" ref="grid" :urlConfig="urlConfig" filterable="inline"
                             @before-grid-cell-render="cellRender"
                             @action-click="actionClick"
                             @grid-data-bound="gridDataBound"
                ></ank-se-grid>
            </div>
            <div>
                <iframe v-if="viewType === 'html'" style="width: 100%; height: 100%;" ref="iframe" :src="viewURL"></iframe>
                <div class="smart-elements-raw-view" v-else-if="viewType=== 'json' || viewType === 'xml'">
                    <pre><code :class="viewType" v-html="viewRawContent"></code></pre>
                </div>
                <component v-else-if="viewType === 'vue' && viewComponent" :is="viewComponent" v-bind="viewComponentProps"></component>
            </div>
        </kendo-splitter>

    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./SmartElements.scss";
</style>
<!-- Global CSS -->
<style scoped lang="scss">
    @import "~highlight.js/styles/github.css";
</style>
<script src="./SmartElements.controller.js"></script>
