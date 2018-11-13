<template>
    <div class="security-se-section">
        <kendo-splitter ref="splitter" orientation="horizontal" style="height:100%" :panes="panes">
            <div>
                <ank-se-grid :pageSizes="[100, 200, 500]" :contextTitles="false" urlConfig="/api/v2/devel/security/profiles/gridConfig" filterable="inline" @before-grid-cell-render="cellRender" @action-click="actionClick">
                </ank-se-grid>
            </div>
            <div style="display: flex; flex: 1">
                <router-multi-view style="display:flex; flex: 1"></router-multi-view>
            </div>
        </kendo-splitter>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Profiles.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./Profiles.controller.js">
</script>
