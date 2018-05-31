<template>
    <div class="admin-center-main">
        <admin-header></admin-header>
        <admin-content></admin-content>
        <admin-notification></admin-notification>
    </div>
</template>
<style>
    body, html {
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

