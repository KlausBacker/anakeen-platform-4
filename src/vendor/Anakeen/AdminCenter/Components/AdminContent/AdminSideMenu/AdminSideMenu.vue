<template>
    <div class="admin-center-side-menu-wrapper">
        <div class="admin-center-side-menu">
            <div class="side-menu-plugin-list">
                <router-link v-for="plugin in plugins" :key="plugin.name" :to="plugin.componentPath">
                    <span class="icon-plugin" v-html="plugin['icon']"></span>
                </router-link>
            </div>
            <div class="side-menu-bottom-content">
                <a class="admin-center-icon-plugin" href="http://docs.anakeen.com" target="_blank">
                    <i class="icon-plugin fa fa-book"></i>
                </a>
                <img class="anakeen-logo" src="/AdminCenter/Images/anakeen_monogramme.svg" alt="Anakeen Logo"/>
            </div>
        </div>
        <div class="admin-center-side-submenu" v-if="enableSubMenu">
            <h4 class="admin-center-plugin-title"><span v-html="currentPlugin['icon']"></span> {{currentPlugin.title}}</h4>
            <div class="admin-center-side-submenu-items">
                <router-link class="admin-center-side-submenu-item" active-class="admin-center-side-submenu-item--active" v-for="submenu in currentPlugin.subcomponents"
                    :key="submenu.name" :to="submenu.componentPath" tag="div" exact>
                    {{submenu.title}}
                </router-link>
            </div>
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
