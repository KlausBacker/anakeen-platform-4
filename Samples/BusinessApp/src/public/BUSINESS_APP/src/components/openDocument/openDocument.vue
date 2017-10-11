<template>
    <div class="documentsList__openDocuments__wrapper" ref="wrapper">
        <div class="documentsList__openDocuments__tabs" ref="tabStrip">
            <ul>
                <li class="documentsList__openDocuments__button">
                    <input ref="newActionButton" class="documentsList__openDocuments__button__newAction" />
                </li>
                <li v-for="d in openedDocuments" :class="`documentsList__openDocuments__tabs__tab${d.initid === activeTab ? ' k-state-active' : ''}`">{{d.title}}</li>
            </ul>
            <div></div>
        </div>
        <div class="documentsList__openDocuments__empty" v-if="!openedDocuments.length">
            <img class="documentsList__openDocuments__empty__img" src="CORE/Images/anakeen-logo.svg"/>
        </div>
    </div>
</template>
<script src="./openDocument.controller.js"></script>
