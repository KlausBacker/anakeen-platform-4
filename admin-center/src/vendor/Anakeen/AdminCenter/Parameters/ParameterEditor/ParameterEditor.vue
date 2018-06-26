<template>
    <div id="edition-window" v-if="editedItem">
        <form>
            <div class="form-group">
                <label for="parameter-description" class="form-label">Description : </label>
                <span class="description-text" id="parameter-description">
                    {{ editedItem.description }}
                </span>
            </div>
            <div class="form-group">
                <label for="parameter-new-value" class="form-label">Value : </label>
                <input :type="parameterInputType" class="form-control value-input" id="parameter-new-value" :value="editedItem.value" v-if="parameterInputType === 'text' || parameterInputType === 'number' || parameterInputType === 'password'">
                <select class="form-control value-input" id="parameter-new-value" :value="editedItem.value" v-else-if="parameterInputType === 'enum'">
                    <option v-for="value in enumPossibleValues">{{ value }}</option>
                </select>
                <!-- TODO JSON -->
            </div>
            <button class="btn btn-primary form-parameter-btn" @click="modifyParameter">Save new value</button>
            <button class="btn btn-secondary form-parameter-btn" @click="closeEditor">Cancel value modification</button>
        </form>
        <div id="confirmation-window" v-show="false" @keyup.enter.stop="closeConfirmationAndEditor">
            <div class="information-text">Parameter successfully modified</div>
            <button class="btn btn-primary form-parameter-btn" @click="closeConfirmationAndEditor">Back to parameters</button>
        </div>
        <div id="error-window" v-show="false" @keyup.enter.stop="closeErrorAndEditor">
            <div class="information-text">Parameter hasn't been modified</div>
            <button class="btn btn-primary form-parameter-btn" @click="closeErrorAndEditor">Back to parameters</button>
        </div>
    </div>
</template>

<style scoped>
    @import './ParameterEditor.css';
</style>

<script src="./ParameterEditor.controller.js"></script>