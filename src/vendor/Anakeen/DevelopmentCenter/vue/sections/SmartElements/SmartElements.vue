<template>
    <div class="smart-elements-section">
        <ank-splitter ref="splitter" class="se-splitter" :panes="panes">
            <template slot="left">
                <ank-se-grid class="se-grid" :pageSizes="[100, 200, 500]" ref="grid" :urlConfig="urlConfig"
                             filterable="inline"
                             @before-grid-cell-render="cellRender"
                             @action-click="actionClick">
                </ank-se-grid>
            </template>
            <template slot="right">
                <div style="display: flex;">
                    <div v-if="splitterSmartElementEmpty" class="smart-element-se-empty">
                        <div>
                            <span class="k-icon k-i-information smart-element-se-empty-icon"></span>
                            <span class="smart-element-se-empty-text"> Please select an element to open </span>
                        </div>
                    </div>
                    <router-multi-view v-else style="display:flex; flex: 1" class="splitter-right"></router-multi-view>
                </div>
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
