<template>
    <!--<div v-if="isDockCollapsed" class="parameter-plugin-collapsed">-->

    <!--</div>-->
    <!--<div v-else-if="isDockExpanded" class="parameter-plugin-expanded">-->

    <!--</div>-->
    <!--<div v-else-if="isHubContent" class="parameter-plugin">-->
    <div class="parameter-plugin">
        <admin-center-global-parameters v-if="globalParameters" @switchParameters="switchParameters"></admin-center-global-parameters>
        <admin-center-user-parameters v-else @switchParameters="switchParameters"></admin-center-user-parameters>
    </div>
</template>

<style>
    @import './AdminCenterParameters.css';
</style>

<script src="./AdminCenterParameters.controller.ts" lang="ts"></script>