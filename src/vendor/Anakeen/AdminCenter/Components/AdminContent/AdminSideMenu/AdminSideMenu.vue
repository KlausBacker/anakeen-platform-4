<template>
    <div class="admin-center-side-menu">
        <div class="side-menu-plugin-list">
            <router-link v-for="plugin in plugins" :key="plugin.name" :to="plugin.componentPath">
                <i :class="`icon-plugin fa fa-${plugin['fa-icon']}`"></i>
            </router-link>
        </div>
        <div class="side-menu-bottom-content">
            <a class="admin-center-icon-plugin" href="http://docs.anakeen.com" target="_blank">
                <i class="icon-plugin fa fa-book"></i>
            </a>
            <img class="anakeen-logo" src="/AdminCenter/Images/anakeen_monogramme.svg" alt="Anakeen Logo"/>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./AdminSideMenu.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./AdminSideMenu.component.js"></script>
