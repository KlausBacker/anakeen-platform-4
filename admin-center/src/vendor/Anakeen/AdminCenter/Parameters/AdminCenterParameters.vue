<template>
    <div id="parameter-plugin" class="parameter-plugin">
        <div id="parameters-tree" class="parameter-tree"></div>
        <ank-parameter-editor :editedItem="editedItem" v-show="false" @closeEditor="editedItem = null"></ank-parameter-editor>
    </div>
</template>

<style>
    @import './AdminCenterParameters.css';
</style>

<script src="./AdminCenterParameters.controller.js"></script>
