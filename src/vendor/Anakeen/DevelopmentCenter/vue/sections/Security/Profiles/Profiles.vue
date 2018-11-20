<template>
    <div class="security-se-section">
        <ank-splitter class="profile-splitter" ref="profileSplitter" :panes="panes">
            <template slot="left">
                <ank-se-grid :pageSizes="[100, 200, 500]" :contextTitles="false" urlConfig="/api/v2/devel/security/profiles/gridConfig" filterable="inline" @before-grid-cell-render="cellRender" @action-click="actionClick">
                </ank-se-grid>
            </template>
            <template slot="right">
                <router-multi-view style="display:flex; flex: 1"></router-multi-view>
            </template>
        </ank-splitter>
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
