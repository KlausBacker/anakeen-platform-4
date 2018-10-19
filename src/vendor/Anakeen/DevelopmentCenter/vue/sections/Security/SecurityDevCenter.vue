<template>
    <div class="security-parent">
        <nav class="security-nav">
            <router-link :to="{name: 'Security::Routes'}">Routes</router-link>
            <router-link :to="{name: 'Security::Roles'}">Roles</router-link>
        </nav>
        <router-multi-view class="security-content"></router-multi-view>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./SecurityDevCenter.scss";
</style>