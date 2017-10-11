<template>
    <div class="documentsList__openDocuments__wrapper" ref="wrapper">
        <div class="documentsList__openDocuments__button">
            <input ref="newActionButton" class="documentsList__openDocuments__button__newAction" />
        </div>
        <div class="documentsList__openDocuments__tabs" ref="tabStrip">
            <ul>
                <li v-for="d in openedDocuments" :class="`documentsList__openDocuments__tabs__tab${d.id === activeTab ? ' k-state-active' : ''}`"></li>
            </ul>
        </div>
        <div class="documentsList__openDocuments__empty" v-if="!openedDocuments.length">
            <img class="documentsList__openDocuments__empty__img" src="CORE/Images/anakeen-logo.svg"/>
        </div>
    </div>
</template>
<script src="./openDocument.controller.js"></script>
