<template>
    <div id="dock-component">
        <div id="dock" :style="'width:' + size">
            <button id="expand-button" class="btn" @click="toggleExpansion">
                <i class="material-icons" v-if="!expanded">keyboard_arrow_right</i>
                <i class="material-icons" v-else>keyboard_arrow_left</i>
            </button>
            <div :id="'tab.' + tab.id" class="dock-tab" :class="selected(tab)" v-for="tab in tabs" :key="tab.id" @click="selectTab(tab.id)">
                <span :id="'compact.' + tab.id" class="dock-tab-compact" :style="'width: ' + compactSize" v-html="tab.compact"></span>
                <span :id="'expanded.' + tab.id" class="dock-tab-expanded" :style="'width: calc(' + largeSize + ' - ' + compactSize + ')'" v-html="tab.expanded" v-if="expanded"></span>
            </div>
        </div>
        <div id="content">
            <div :id="'content.' + tab.id" class="dock-tab-content" v-for="tab in tabs" :key="tab.id" v-html="tab.content" v-show="tab.id === selectedTab"></div>
        </div>
        <div style="display: none" id="originalDom">
            <slot></slot>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './Dock.scss';
</style>

<script src="./Dock.component.js"></script>
