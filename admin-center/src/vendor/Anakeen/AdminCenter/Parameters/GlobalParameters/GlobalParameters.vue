<template>
    <div class="global-parameters">
        <div class="parameters-tree"></div>
        <admin-center-parameter-editor :editedItem="editedItem" :editRoute="editRoute" v-show="false" @closeEditor="updateAtEditorClose"></admin-center-parameter-editor>
        <div class="value-displayer" v-show="false"></div>
    </div>
</template>

<script src="./GlobalParameters.controller.js"></script>

<style scoped>
    @import "./GlobalParameters.css";
</style>