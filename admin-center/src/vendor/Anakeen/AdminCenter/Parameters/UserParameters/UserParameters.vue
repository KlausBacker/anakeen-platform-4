<template>
    <div id="user-parameters" class="user-parameters">
        <div id="user-search" class="user-search">
            <form class="user-search-form">
                <div class="form-group search-group">
                    <a class="switch-parameters" @click="switchParameters">System parameters</a>
                    <div class="input-group">
                        <input autofocus id="user-search-input" class="form-control"  v-model.trim="inputSearchValue" placeholder="Search user by login, first name, or last name...">
                        <div class="input-group-append reset-button">
                            <i class="input-group-addon material-icons reset-search-btn" @click="clearSearchInput">close</i>
                        </div>
                        <div class="input-group-append">
                            <button id="user-search-btn" class="btn btn-primary" @click.stop="searchUser" :disabled="isSearchButtonDisabled">Search</button>
                        </div>
                    </div>
                </div>
            </form>
            <div id="users-tree" class="users-tree"></div>
            <div class="delete-confirmation-window" v-show="false" @keyup.enter.stop="closeDeleteConfirmation">
                <div class="information-text">Parameter successfully restored to system value</div>
                <a class="delete-confirmation-btn form-parameter-btn" @click="closeDeleteConfirmation">Back to parameters</a>
            </div>
            <div class="delete-error-window" v-show="false" @keyup.enter.stop="closeDeleteError">
                <div class="information-text">An error occured, parameter modification was not saved</div>
                <a class="delete-error-btn form-parameter-btn" @click="closeDeleteError">Back to parameters</a>
            </div>
        </div>
        <div id="parameters-div" class="parameters-div">
            <div id="user-parameters-tree" class="user-parameters-tree"></div>
            <admin-center-parameters-editor :edited-item="editedItem" :editRoute="editRoute" @closeEditor="updateAtEditorClose"></admin-center-parameters-editor>
        </div>
    </div>
</template>

<script src="./UserParameters.controller.js"></script>

<style scoped>
    @import "./UserParameters.css";
</style>