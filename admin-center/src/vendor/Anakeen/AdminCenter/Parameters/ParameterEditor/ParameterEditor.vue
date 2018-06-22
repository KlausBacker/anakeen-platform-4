<template>
    <form>
        <div class="form-group row">
            <label for="parameter-name" class="col-sm-2 col-form-label">Name : </label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="parameter-name" :value="editedItem.name">
            </div>
        </div>
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
            <label for="parameter-category" class="col-sm-2 col-form-label">Category : </label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="parameter-category" :value="editedItem.category">
            </div>
        </div>
        <div class="form-group row">
            <label for="parameter-type" class="col-sm-2 col-form-label">Value type : </label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="parameter-type" :value="formatedParameterType">
            </div>
        </div>
        <div class="form-group row">
            <label for="parameter-value" class="col-sm-2 col-form-label">Actual value : </label>
            <div class="col-sm-10">
                <input type="text" readonly class="form-control-plaintext" id="parameter-value" :value="editedItem.value">
            </div>
        </div>
        <div class="form-group row">
            <label for="parameter-new-value" class="col-sm-2 col-form-label">New value : </label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="parameter-new-value" :value="editedItem.value" v-if="editedItem.type === 'text'">
                <input type="number" class="form-control" id="parameter-new-value" :value="editedItem.value" v-else-if="editedItem.type === 'number'">
                <!-- TODO Difference between Number double integer -->
                <!-- TODO Enum -->
                <!-- TODO JSON -->
            </div>
        </div>
        <button class="btn btn-primary">Save new value</button>
        <button class="btn btn-secondary">Cancel value modification</button>
    </form>
</template>

<style scoped>
    @import './ParameterEditor.css';
</style>

<script src="./ParameterEditor.controller.js"></script>