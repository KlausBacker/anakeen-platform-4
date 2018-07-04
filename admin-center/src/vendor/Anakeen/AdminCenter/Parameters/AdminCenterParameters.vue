<template>
    <div id="parameter-plugin" class="parameter-plugin">
        <button @click="switchParameters">Switch</button>
        <admin-center-global-parameters v-if="globalParameters"></admin-center-global-parameters>
        <admin-center-user-parameters v-else></admin-center-user-parameters>
    </div>
</template>

<style>
    @import './AdminCenterParameters.css';
</style>

<script src="./AdminCenterParameters.controller.js"></script>
