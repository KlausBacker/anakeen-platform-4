<template>
    <div class="smart-elements-section">
        <kendo-splitter ref="splitter" orientation="horizontal" style="height:100%" :panes="panes">
            <div>
                <ank-se-grid ref="grid" :urlConfig="urlConfig" filterable="inline" @before-grid-cell-render="cellRender" @action-click="actionClick"></ank-se-grid>
            </div>
            <div>
                <iframe v-if="viewType === 'html'" style="width: 100%; height: 100%;" ref="iframe" :src="viewURL"></iframe>
                <div v-else-if="viewType=== 'json' || viewType === 'xml'">
                    <pre>{{viewRawContent}}</pre>
                </div>
            </div>
        </kendo-splitter>

    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./SmartElements.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./SmartElements.controller.js"></script>
