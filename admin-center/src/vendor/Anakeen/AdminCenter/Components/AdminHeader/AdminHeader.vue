<template>
    <header class="admin-center-header">
        <div class="header-content header-left-content">
            <span class="header-app-title">{{appName}}</span> <span>-</span> <span class="header-app-title">{{adminTitle}}</span>
        </div>
        <div class="header-content header-right-content">
            <span class="header-admin-user">
                {{userFullName}}
            </span>
            <ank-logout></ank-logout>
        </div>
    </header>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./AdminHeader.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./AdminHeader.component.js"></script>
