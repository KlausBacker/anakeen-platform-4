<template>
    <div>
        <div id="compactFragment">
            <slot name="compact"><span>[-]</span></slot>
        </div>
        <div id="expandedFragment">
            <slot  name="expanded"><span>[Empty]</span></slot>
        </div>
        <div id="contentFragment">
            <slot name="content"><div>[No content]</div></slot>
        </div>
    </div>
</template>

<style scoped>

</style>

<script src="./DockTab.component.js"></script>