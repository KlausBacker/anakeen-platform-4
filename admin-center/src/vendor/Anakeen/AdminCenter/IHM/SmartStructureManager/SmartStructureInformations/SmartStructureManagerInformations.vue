<template>
    <div class="smart-structure-infos-card">
        <div class="infos-card-title">Properties</div>
        <div class="infos-card-content">
            <properties-view :elementId="ssName"></properties-view>
        </div>
    </div>
</template>
<style lang="scss">
    @import "./SmartStructureManagerInformations.scss";
</style>
<script src="./SmartStructureManagerInformations.controller.ts" lang="ts"></script>