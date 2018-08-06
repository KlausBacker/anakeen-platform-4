<template>
    <div class="admin-center-main">
        <admin-header></admin-header>
        <admin-content></admin-content>
        <admin-notification></admin-notification>
        <admin-modal></admin-modal>
    </div>
</template>
<style lang="scss">
    html, body {
        font-size: 12px;
        width: 100vw;
        height: 100vh;
        display: flex;
        margin: 0;
    }
    .admin-center-main {
        display: flex;
        flex-direction: column;
        flex:1;
    }
</style>
<script src="./AdminCenter.controller.js">
</script>

