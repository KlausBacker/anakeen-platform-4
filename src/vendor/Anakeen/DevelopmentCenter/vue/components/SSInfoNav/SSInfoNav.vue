<template>
    <div class="ss-infos-nav">
        <a data-role="develRouterLink" v-for="(info, index) in infoSections" :key="index" class="infos-nav-item" :href="info.path"><i class="material-icons infos-nav-icon rotate">navigation</i> {{info.label}}</a>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./SSInfoNav.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./SSInfoNav.controller.js"></script>
