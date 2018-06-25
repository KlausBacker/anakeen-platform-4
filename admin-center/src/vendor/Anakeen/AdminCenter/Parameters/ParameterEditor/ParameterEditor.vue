<template>
    <div id="edition-window" v-if="editedItem">
        <form>
            <div class="form-group row">
                <label for="parameter-description" class="col-sm-2 col-form-label">Description : </label>
                <div class="col-sm-10">
                    <input type="text" readonly class="form-control-plaintext" id="parameter-description" :value="editedItem.description">
                </div>
            </div>
            <div class="form-group row" v-if="editedItem.domainId !== 1 && editedItem.domainName !== 'CORE'">
                <label for="parameter-domain" class="col-sm-2 col-form-label">Domain : </label>
                <div class="col-sm-10">
                    <input type="text" readonly class="form-control-plaintext" id="parameter-domain" :value="editedItem.domainName">
                </div>
            </div>
            <div class="form-group row">
                <label for="parameter-new-value" class="col-sm-2 col-form-label">New value : </label>
                <div class="col-sm-10">
                    <input :type="inputType" class="form-control" id="parameter-new-value" :value="editedItem.value" v-if="parameterInputType === 'text' || parameterInputType === 'number'">
                    <select class="form-control" id="parameter-new-value" :value="editedItem.value" v-else-if="parameterInputType === 'enum'">
                        <option v-for="value in enumPossibleValues">{{ value }}</option>
                    </select>
                    <!-- TODO JSON -->
                </div>
            </div>
            <button class="btn btn-primary" @click="modifyParameter">Save new value</button>
            <button class="btn btn-secondary" @click="closeEditor">Cancel value modification</button>
        </form>
    </div>
</template>

<style scoped>
    @import './ParameterEditor.css';
</style>

<script src="./ParameterEditor.controller.js"></script>