<template>
    <section class="admin-center-main-content">
        <admin-side-menu :modules="getModulesList"></admin-side-menu>
        <router-view></router-view>
    </section>
</template>
<style lang="scss">
    @import './AdminContent.scss';
</style>
<script src="./AdminContent.controller.js">
</script>

