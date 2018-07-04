<template>
    <div id="global-parameters" class="global-parameters">
        <div id="parameters-tree" class="parameters-tree"></div>
        <admin-center-parameter-editor :editedItem="editedItem" :editRoute="editRoute" v-show="false" @closeEditor="updateAtEditorClose"></admin-center-parameter-editor>
    </div>
</template>

<script src="./GlobalParameters.controller.js"></script>

<style scoped>
    @import "./GlobalParameters.css";
</style>