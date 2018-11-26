<template>
    <div class="admin-center-side-menu-wrapper">
        <div class="admin-center-side-menu">
            <div class="side-menu-plugin-list">
                <router-link v-for="plugin in plugins" :key="plugin[pluginSchema.name]" :to="plugin[pluginSchema.pluginPath]">
                    <span class="icon-plugin" :title="plugin[pluginSchema.title] || 'Admin Plugin'" v-html="plugin[pluginSchema.icon]"></span>
                </router-link>
            </div>
            <div class="side-menu-bottom-content">
                <!--<a class="admin-center-icon-plugin" href="http://docs.anakeen.com" target="_blank">-->
                    <!--<span class="icon-plugin" title="Documentation">-->
                        <!--<i class="material-icons">book</i>-->
                    <!--</span>-->
                <!--</a>-->
                <img class="anakeen-logo" src="/AdminCenter/Images/anakeen_monogramme.svg" alt="Anakeen Logo"/>
            </div>
        </div>
        <div class="admin-center-side-submenu" v-if="enableSubMenu">
            <h4 class="admin-center-plugin-title"><span class="admin-center-plugin-icon-wrapper" v-html="rootPlugin[pluginSchema.icon]"></span> {{rootPlugin[pluginSchema.title]}}</h4>
            <div class="admin-center-side-submenu-items">
                <router-link class="admin-center-side-submenu-item" active-class="admin-center-side-submenu-item--active" v-for="submenu in rootPlugin[pluginSchema.subcomponents]"
                             :key="submenu[pluginSchema.name]" :to="submenu[pluginSchema.pluginPath]" tag="div" exact>
                    <span v-if="submenu[pluginSchema.icon]" class="admin-center-plugin-icon-wrapper" v-html="submenu[pluginSchema.icon]"></span> {{submenu.title}}
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
