<template>
    <div :class="{ 'hub-dock-entry-component': true, 'entry-selected': entrySelected}" :style="entryStyle" @click="selectEntry">
        <slot v-if="isCollapsed" name="collapsedContent"></slot>
        <slot v-else name="expandedContent"></slot>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./HubDockEntry.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script lang="ts" src="./HubDockEntry.component.ts"></script>
