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
<!--                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1" class="splitter-right"></router-multi-view>-->
                <component style="height:100%" v-if="selectedElement" :is="selectedElement.component" v-bind="selectedElement.props"></component>
            </template>
        </ank-splitter>
    </div>
</template>
<!-- Global CSS -->
<style scoped lang="scss">
    @import "./SmartElements.scss";
</style>
<script src="./SmartElements.controller.js"></script>
