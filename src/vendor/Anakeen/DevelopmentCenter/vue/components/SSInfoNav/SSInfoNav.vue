<template>
    <div class="ss-infos-nav">
        <router-link v-for="(info, index) in infoSections" :key="index" class="infos-nav-item" :to="info.path"><i class="material-icons infos-nav-icon rotate">navigation</i> {{info.label}}</router-link>
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
