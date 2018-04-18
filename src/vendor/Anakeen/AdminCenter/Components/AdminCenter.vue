<template>
    <div class="admin-center-main">
        <admin-header></admin-header>
        <section class="admin-center-main-content">
            <admin-side-menu></admin-side-menu>
            <router-view></router-view>
        </section>
    </div>
</template>
<style>
    body, html {
        margin: 0;
    }

    .admin-center-main {
        width: 100%;
        height: 100%;
    }

    .admin-center-main-content {
        display: flex;
        height: calc(100% - 35px);
    }
</style>
<script src="./AdminCenter.controller.js">
</script>

