<template>
    <div class="documentsList__openDocuments__wrapper" ref="wrapper">
        <ul ref="newActionButton"></ul>
        <div class="documentsList__openDocuments__empty" v-if="emptyData">
            <img class="documentsList__openDocuments__empty__img" src="CORE/Images/anakeen-logo.svg"/>
        </div>
        <a4-documents-tabs v-show="!emptyData" v-model="openedDocuments" :closable="true">
        </a4-documents-tabs>
    </div>
</template>
<style lang="scss">
    @import './openDocument.scss';
</style>
<script src="./openDocument.controller.js"></script>