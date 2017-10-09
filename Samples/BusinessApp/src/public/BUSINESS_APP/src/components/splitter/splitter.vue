<template>
    <div ref="splitter" class="documentsList__panel__wrapper">
        <slot class="leftPane" name="leftPane"></slot>
        <div class="documentsList__collapsible__handler documentsList__collapsible__handler--panel documentsList__height_ignored">
            <div class="documentsList__collapsible__handler__label">{{collection ? collection.html_label : ''}}</div>
        </div>
        <slot></slot>
    </div>
</template>
<script src="./splitter.controller.js"></script>

