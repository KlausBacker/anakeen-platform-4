<template>
    <div id="user-parameters" class="user-parameters">
        <div id="user-search" class="user-search">
            <div>To change a specific user parameter, please select a user :</div>
            <form>
                <div class="form-group">
                    <label for="user-search-input">Search a user</label>
                    <input id="user-search-input" class="form-control" placeholder="Search...">
                </div>
                <button id="user-search-btn" class="btn btn-primary" @click="searchUser">Search</button>
            </form>
            <div id="users-tree" class="users-tree"></div>
        </div>
        <div id="user-parameters-tree" class="user-parameters-tree"></div>
        <admin-center-parameters-editor :edited-item="editedItem"></admin-center-parameters-editor>
    </div>
</template>

<script src="./UserParameters.controller.js"></script>

<style scoped>
    @import "./UserParameters.css";
</style>