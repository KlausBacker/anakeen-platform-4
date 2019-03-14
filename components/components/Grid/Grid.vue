<template>
    <div ref="gridWrapper" class="smart-element-grid">
        <div class="grid-widget-wrapper">
            <div class="grid-widget" ref="kendoGrid"></div>
        </div>
        <grid-dialog ref="gridColumnsDialog" :colsConfig="colsConfig" @change="onSettingsChange"></grid-dialog>
    </div>
</template>
<!-- CSS to this component only -->
<style lang="scss">
    @import "./Grid.scss";
</style>

<script src="./Grid.controller.ts" lang="ts">
</script>
