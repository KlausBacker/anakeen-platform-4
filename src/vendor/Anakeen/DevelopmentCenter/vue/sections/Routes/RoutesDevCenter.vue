<template>
    <div class="routes-parent">
        <nav class="routes-nav">
            <router-link :to="{name: 'Security::Routes::RoutesAcl'}">Access Control</router-link>
            <router-link :to="{name: 'Security::Routes::RoutesPermissions'}">Permissions</router-link>
        </nav>
        <router-multi-view class="routes-content"></router-multi-view>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./RoutesDevCenter.scss";
</style>