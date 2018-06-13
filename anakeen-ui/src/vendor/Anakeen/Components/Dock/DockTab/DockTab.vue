<template>
    <div>
        <div id="compactFragment">
            <slot name="compact-label"></slot>
        </div>
        <div id="expandedFragment">
            <slot  name="expanded-label"></slot>
        </div>
        <div id="contentFragment">
            <slot name="content"></slot>
        </div>
    </div>
</template>

<style scoped>

</style>

<script src="./DockTab.component.js"></script>