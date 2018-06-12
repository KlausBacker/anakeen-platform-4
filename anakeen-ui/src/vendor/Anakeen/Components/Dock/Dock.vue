<template>
    <div id="dock-component" :position="position">
        <div id="dock" :style="dockSizeStyle">
            <div :id="'header.' + component.id" class="header-component" :style="headerSizeStyle" v-for="component in headerComponents" :key="component.id">
                <span :id="'header-compact.' + component.id" class="header-component-compact" :style="compactSizeStyle" v-html="component.compact"></span>
                <span :id="'header-expanded.' + component.id" class="header-component-expanded" :style="expandedSizeStyle" v-html="component.expanded" v-if="expandedDock || position === 'top' || position === 'bottom'"></span>
            </div>
            <button id="expand-button" class="btn" :style="buttonSizeStyle" v-if="expandable" @click="toggleExpansion">
                <i class="material-icons" v-if="(position === 'right' && expandedDock) || (position !== 'right' && !expandedDock)">keyboard_arrow_right</i>
                <i class="material-icons" v-if="(position === 'right' && !expandedDock) || (position !== 'right' && expandedDock)">keyboard_arrow_left</i>
            </button>
            <div :id="'tab.' + tab.id" class="dock-tab" :class="selected(tab)" :style="tabSizeStyle" v-for="tab in tabs" :key="tab.id" @click="selectTab(tab.id)">
                <span :id="'compact.' + tab.id" class="dock-tab-compact" :style="compactSizeStyle" v-html="tab.compact"></span>
                <span :id="'expanded.' + tab.id" class="dock-tab-expanded" :style="expandedSizeStyle" v-html="tab.expanded" v-if="expandedDock"></span>
            </div>
            <div class="footer-components">
                <div :id="'footer.' + component.id" class="footer-component" :style="footerSizeStyle" v-for="component in footerComponents" :key="component.id">
                    <span :id="'footer-compact.' + component.id" class="footer-component-compact" :style="compactSizeStyle" v-html="component.compact"></span>
                    <span :id="'footer-expanded.' + component.id" class="footer-component-expanded" :style="expandedSizeStyle" v-html="component.expanded" v-if="expandedDock && (position === 'left' || position === 'right')"></span>
                </div>
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
