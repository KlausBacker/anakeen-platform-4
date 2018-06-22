<template>
    <div id="parameter-plugin" class="parameter-plugin">
        <div id="parameters-tree" class="parameter-tree"></div>
        <div id="edition-window" style="display: none;">
            <div v-if="editedItem">
                <ank-parameter-editor :editedItem="editedItem"></ank-parameter-editor>
            </div>
        </div>
    </div>
</template>

<style>
    @import './AdminCenterParameters.css';
</style>

<script src="./AdminCenterParameters.controller.js"></script>
