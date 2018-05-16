<template>
    <section class="admin-center-main-content">
        <admin-side-menu :plugins="getPluginsList"></admin-side-menu>
        <keep-alive>
            <router-view>
                <router-view></router-view>
            </router-view>
        </keep-alive>
    </section>
</template>
<style lang="scss">
    @import './AdminContent.scss';
</style>
<script src="./AdminContent.controller.js">
</script>

