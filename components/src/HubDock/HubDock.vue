<template>
    <div :class="[{'hub-dock-component': true, 'hub-dock--expanded': !collapsed, 'hub-dock--collapsed': collapsed}, `hub-dock--${position.toLowerCase()}`]" :style="dockStyle">
        <div class="hub-dock-header">
            <slot name="header"></slot>
        </div>
        <div v-if="expandable" class="hub-dock-collapse-button" @click="toggleDock">
            <i :class="`fa fa-${faCollapseIcon}`"></i>
        </div>
        <div class="hub-dock-center">
            <div class="hub-dock-entries" :style="entriesStyle">
                <slot></slot>
            </div>
        </div>
        <div class="hub-dock-footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./HubDock.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./HubDock.component.ts"></script>
