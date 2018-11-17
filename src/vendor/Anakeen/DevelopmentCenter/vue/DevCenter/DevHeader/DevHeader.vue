<template>
    <header class="dev-center-header">
        <div class="header-content header-left-content">
            <!--<span class="header-app-title vendor-name">{{vendorName}}</span>-->
            <vendor-selector></vendor-selector>
            <div class="header-breadcrumb">
                <span v-for="(routeItem, index) in routesSections" :key="index">
                    <span :class="{ 'header-breadcrumb-root-item': index === 0, 'header-breacrumb-item': true }">
                        {{getRouteLabel(routeItem)}}
                    </span>
                    <span class="header-breadcrumb-separator" v-if="index !== routesSections.length - 1"> > </span>
                </span>

            </div>
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
