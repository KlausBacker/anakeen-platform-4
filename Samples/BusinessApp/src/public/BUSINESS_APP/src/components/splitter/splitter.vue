<template>
    <div ref="splitter" class="documentsList__splitter__wrapper">
        <div id="leftPane" class="leftPane">
            <slot name="leftPane"></slot>
        </div>
        <div class="documentsList__collapsible__handler" @click="onCollapseSplitter">
            <div class="documentsList__collapsible__handler__label">{{collection ? collection.html_label : ''}}</div>
        </div>
        <div id="mainPane" class="mainPane">
            <slot name="mainPane"></slot>
        </div>
    </div>
</template>
<style lang="scss">
    @import './splitter.scss';
</style>
<script src="./splitter.controller.js"></script>

