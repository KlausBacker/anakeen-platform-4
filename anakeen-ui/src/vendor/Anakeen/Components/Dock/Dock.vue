<template>
    <div id="dock-component" :position="position">
        <div id="dock" :style="dockSizeStyle">
            <button id="expand-button" class="btn" v-if="expandable" @click="toggleExpansion">
                <i class="material-icons" v-if="(position === 'right' && expanded) || (position !== 'right' && !expanded)">keyboard_arrow_right</i>
                <i class="material-icons" v-if="(position === 'right' && !expanded) || (position !== 'right' && expanded)">keyboard_arrow_left</i>
            </button>
            <div :id="'tab.' + tab.id" class="dock-tab" :class="selected(tab)" v-for="tab in tabs" :key="tab.id" @click="selectTab(tab.id)">
                <span :id="'compact.' + tab.id" class="dock-tab-compact" :style="compactSizeStyle" v-html="tab.compact"></span>
                <span :id="'expanded.' + tab.id" class="dock-tab-expanded" :style="expandedSizeStyle" v-html="tab.expanded" v-if="expanded"></span>
            </div>
        </div>
        <div id="content">
            <div :id="'content.' + tab.id" class="dock-tab-content" v-for="tab in tabs" :key="tab.id" v-html="tab.content" v-show="tab.id === selectedTab"></div>
        </div>
        <div :v-show="false" id="originalDom">
            <slot></slot>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './Dock.scss';
</style>

<script src="./Dock.component.js"></script>
