<template>
    <header class="dev-center-header">
        <div class="header-content header-left-content">
            <span class="header-app-title vendor-name">{{vendorName}}</span> <span>-</span>
            <span class="header-app-title">{{appTitle}}</span>
            <!--<span v-if="rootPlugin">></span> <span v-if="rootPlugin" class="header-app-title">{{rootPlugin.title||''}}</span>-->
        </div>
        <div class="header-content header-right-content">
        </div>
    </header>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./DevHeader.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./DevHeader.component.js"></script>
