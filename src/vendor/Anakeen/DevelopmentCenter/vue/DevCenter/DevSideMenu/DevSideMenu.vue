<template>
    <div class="dev-center-side-menu-wrapper">
        <div class="side-menu">
            <div class="side-menu-sections-list">
                <router-link class="dev-center-section-link" v-for="(section, index) in sections" :key="`section-link-${index}`" :to="section" tag="div">{{section.label||section.name}}</router-link>
            </div>
            <div class="side-menu-bottom-content">
            </div>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./DevSideMenu.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./DevSideMenu.component.js"></script>
