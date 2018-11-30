<template>
    <div class="security-fall-section">
        <ank-splitter class="fall-splitter" ref="fallSplitter" :panes="panes" localStorageKey="security-fall-splitter">
            <template slot="left">
                <ank-se-grid ref="fallGrid"
                             :pageSizes="[100, 200, 500]"
                             :contextTitles="false"
                             urlConfig="/api/v2/devel/security/fieldAccess/gridConfig"
                             filterable="inline" @action-click="actionClick"
                             @before-grid-cell-render="cellRender"
                >
                </ank-se-grid>
            </template>
            <template slot="right">
                <router-multi-view :force-multi-views="false" style="display:flex; flex: 1" class="splitter-right"></router-multi-view>
            </template>
        </ank-splitter>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./FieldAccess.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./FieldAccess.controller.js"></script>
