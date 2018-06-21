<template>
    <div id="parameter-plugin" class="parameter-plugin">
        <div id="filter-bar" class="filter-bar">
            <div id="general-bar">
                <div id="tree-toolbar"></div>
            </div>
            <div id="global-domain-bar">
                <h3>Global and domain parameters</h3>
                <input id="search-input" class="form-control" type="text" placeholder="Search" v-model="researchTerms" @keyup="searchParameters">
            </div>
            <div id="user-bar" v-show="false"> <!-- TODO Show when necessary -->
                <h3>User parameters</h3>
                <input id="search-user-input" class="form-control" type="text" placeholder="Search user"/>
                <button class="btn" @click="searchUsersParameters">Search user</button>
            </div>
        </div>
        <div id="parameters-tree" class="parameter-tree"></div>
    </div>
</template>

<style>
    @import './AdminCenterParameters.css';
</style>

<script src="./AdminCenterParameters.controller.js"></script>
