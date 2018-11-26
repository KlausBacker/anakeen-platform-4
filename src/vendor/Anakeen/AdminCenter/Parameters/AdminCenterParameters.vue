<template>
    <div class="parameter-plugin">
        <admin-center-global-parameters v-if="globalParameters" @switchParameters="switchParameters"></admin-center-global-parameters>
        <admin-center-user-parameters v-else @switchParameters="switchParameters"></admin-center-user-parameters>
    </div>
</template>

<style>
    @import './AdminCenterParameters.css';
</style>

<script src="./AdminCenterParameters.controller.js"></script>
