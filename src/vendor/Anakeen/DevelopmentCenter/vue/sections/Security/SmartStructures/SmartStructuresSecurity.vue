<template>
    <div class="security-ss-section">
        <div class="security-ss-section-title">
            <h3>Smart Structure Security Configuration</h3>
        </div>
        <div class="security-ss-section-content">
            <ss-list vendorCategory="auto" routeName="Security::SmartStructures::name" routeParamField="ssName"></ss-list>
        </div>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./SmartStructuresSecurity.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script src="./SmartStructuresSecurity.controller.js"></script>
