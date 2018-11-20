<template>
    <div class="smart-elements-section">
        <kendo-splitter ref="splitter" orientation="horizontal" style="height:100%" :panes="panes">
            <div>
                <ank-se-grid class="se-grid" :pageSizes="[100, 200, 500]" ref="grid" :urlConfig="urlConfig" filterable="inline"
                             @before-grid-cell-render="cellRender"
                             @action-click="actionClick"
                ></ank-se-grid>
            </div>
            <div>
                <router-multi-view class="smart-elements-view"></router-multi-view>
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
