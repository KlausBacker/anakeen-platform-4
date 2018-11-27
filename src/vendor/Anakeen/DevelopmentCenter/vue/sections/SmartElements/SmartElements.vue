<template>
    <div class="smart-elements-section">
        <ank-splitter ref="splitter" class="se-splitter" :panes="panes" localStorageKey="smart-elements-splitter">
            <template slot="left">
                <ank-se-grid class="se-grid" :pageSizes="[100, 200, 500]" ref="grid" :urlConfig="urlConfig"
                             filterable="inline"
                             @before-grid-cell-render="cellRender"
                             @action-click="actionClick">
                </ank-se-grid>
            </template>
            <template slot="right">
                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1" class="splitter-right"></router-multi-view>
            </template>
        </ank-splitter>
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
