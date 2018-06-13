<template>
    <div id="dock-component" :position="position">
        <div id="dock" :style="dockSizeStyle">
            <div :id="'header.' + tab.id" class="header-component" :class="privateScope.selectedSelectable(tab)" :style="headerSizeStyle" v-for="tab in headerTabs" :key="tab.id" @click="selectTabWithId(tab.id)">
                <span :id="'header-compact.' + tab.id" class="header-component-compact" :style="'width: ' + compactSize" v-html="tab.compact"></span>
                <span :id="'header-expanded.' + tab.id" class="header-component-expanded" :style="expandedSizeStyle" v-html="tab.expanded" v-if="expandedDock || position === 'top' || position === 'bottom'"></span>
            </div>
            <button id="expand-button" class="btn selectable" :style="buttonSizeStyle" v-if="expandable" @click="toggleExpansion">
                <i class="material-icons" v-if="(position === 'right' && expandedDock) || (position !== 'right' && !expandedDock)">keyboard_arrow_right</i>
                <i class="material-icons" v-if="(position === 'right' && !expandedDock) || (position !== 'right' && expandedDock)">keyboard_arrow_left</i>
            </button>
            <div :id="'tab.' + tab.id" class="dock-tab" :class="privateScope.selectedSelectable(tab)" :style="tabSizeStyle" v-for="tab in tabs" :key="tab.id" @click="selectTabWithId(tab.id)">
                <span :id="'compact.' + tab.id" class="dock-tab-compact" :style="'width: ' + compactSize" v-html="tab.compact"></span>
                <span :id="'expanded.' + tab.id" class="dock-tab-expanded" :style="expandedSizeStyle" v-html="tab.expanded" v-if="expandedDock"></span>
            </div>
            <div class="footer-components">
                <div :id="'footer.' + tab.id" :class="privateScope.selectedSelectable(tab)" class="footer-component" :style="footerSizeStyle" v-for="tab in footerTabs" :key="tab.id" @click="selectTabWithId(tab.id)">
                    <span :id="'footer-compact.' + tab.id" class="footer-component-compact" :style="'width: ' + compactSize" v-html="tab.compact"></span>
                    <span :id="'footer-expanded.' + tab.id" class="footer-component-expanded" :style="expandedSizeStyle" v-html="tab.expanded" v-if="expandedDock && (position === 'left' || position === 'right')"></span>
                </div>
            </div>
        </div>
        <div id="content" v-show="contentDisplayed">
            <div :id="'content.' + tab.id" class="dock-tab-content" v-for="tab in headerTabs" :key="tab.id" v-html="tab.content" v-show="tab.content && (tab.id === selectedTab)"></div>
            <div :id="'content.' + tab.id" class="dock-tab-content" v-for="tab in tabs" :key="tab.id" v-html="tab.content" v-show="tab.content && (tab.id === selectedTab)"></div>
            <div :id="'content.' + tab.id" class="dock-tab-content" v-for="tab in footerTabs" :key="tab.id" v-html="tab.content" v-show="tab.content && (tab.id === selectedTab)"></div>
        </div>
        <div v-show="false" id="original-dom">
            <slot></slot>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './Dock.scss';
</style>

<script src="./Dock.component.js"></script>
