<template>
    <div ref="splitter" class="documentsList__splitter__wrapper">
        <div class="leftPane">
            <slot name="leftPane"></slot>
            <div class="documentsList__collapsible__handler documentsList__collapsible__handler--panel" @click="onCollapseSplitter">
                <div class="documentsList__collapsible__handler__label">{{collection ? collection.html_label : ''}}</div>
            </div>
        </div>
        <div class="mainPane">
            <slot name="mainPane"></slot>
        </div>
    </div>
</template>
<script src="./splitter.controller.js"></script>

