<template>
    <div id="parameter-plugin" class="parameter-plugin">
        <admin-center-global-parameters :data-source="allParametersDataSource"></admin-center-global-parameters>
        <admin-center-user-parameters></admin-center-user-parameters>
    </div>
</template>

<style>
    @import './AdminCenterParameters.css';
</style>

<script src="./AdminCenterParameters.controller.js"></script>
