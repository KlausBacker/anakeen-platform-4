<template>
    <div class="edition-window">
        <form v-if="editedItem">
            <div class="form-group">
                <div class="form-label">Description : </div>
                <span class="description-text">
                    {{ editedItem.description }}
                </span>
            </div>
            <div class="form-group">
                <div class="form-label">Value : </div>
                <input :type="parameterInputType" class="form-control value-input parameter-new-value" :value="inputSelectedValue" v-if="parameterInputType === 'text' || parameterInputType === 'number' || parameterInputType === 'password'" title="value">
                <select class="value-input parameter-new-value enum-drop-down" :value="inputSelectedValue" v-else-if="parameterInputType === 'enum'" title="value">
                    <option v-for="value in enumPossibleValues">{{ value }}</option>
                </select>
                <div class="json-editor" v-else-if="isJson(inputSelectedValue)"></div>
                <div v-else>
                    <div class="alert alert-warning invalid-json-warning" role="alert">Parameter is not a valid json, please save it as json</div>
                    <textarea class="form-control parameter-new-value" :value="inputSelectedValue" title="value"></textarea>
                </div>
            </div>
            <a class="modify-btn form-parameter-btn" @click="modifyParameter">Save new value</a>
            <a class="cancel-btn form-parameter-btn" @click="closeEditor">Cancel value modification</a>
        </form>
        <div class="confirmation-window" v-show="false" @keyup.enter.stop="closeConfirmationAndEditor">
            <div class="information-text">Parameter successfully modified</div>
            <a class="close-confirmation-btn form-parameter-btn" @click="closeConfirmationAndEditor">Back to parameters</a>
        </div>
        <div class="error-window" v-show="false" @keyup.enter.stop="closeErrorAndEditor">
            <div class="information-text">An error occurred during parameter modification</div>
            <a class="close-error-btn form-parameter-btn" @click="closeErrorAndEditor">Back to parameters</a>
        </div>
    </div>
</template>

<style scoped>
    @import './ParameterEditor.css';
</style>

<script src="./ParameterEditor.controller.js"></script>