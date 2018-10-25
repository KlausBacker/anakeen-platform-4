<template>
    <div class="router-tabs">
        <nav class="router-tabs-nav">
            <router-link class="router-tab-nav-link" v-for="item in items" :key="`router-tab-nav-${item.name}`" :to="{name: item.name}">{{item.label}}</router-link>
        </nav>
        <router-multi-view class="router-tabs-content"></router-multi-view>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./RouterTabs.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./RouterTabs.controller.js"></script>
