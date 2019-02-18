<template>
    <div class="dock-component" :position="position">
        <div class="dock" :style="dockSizeStyle" :class="superposeDockClass">
            <div class="header-tab" :class="privateScope.selectedSelectable(tab)" :style="headerSizeStyle" v-for="tab in headerTabs" :key="tab.id" @click="selectTabWithId(tab.id)">
                <span class="header-tab-compact" :style="'width: ' + compactSize" v-html="tab.compact" v-if="typeof tab.compact === 'string'"></span>
                <div class="header-tab-compact" :style="'width: ' + compactSize" v-else>
                    <component :is="tab.compact.componentName" v-bind="tab.compact.props"></component>
                </div>
                <div class="header-tab-expanded" :style="expandedSizeStyle" v-if="expandedDock || position === 'top' || position === 'bottom'">
                    <span v-html="tab.expanded" v-if="typeof tab.expanded === 'string'"></span>
                    <div v-else>
                        <component :is="tab.expanded.componentName" v-bind="tab.expanded.props"></component>
                    </div>
                </div>
            </div>
            <button class="btn selectable expand-button" :style="buttonSizeStyle" v-if="expandable" @click="toggleExpansion">
                <i class="material-icons" v-if="(position === 'right' && expandedDock) || (position !== 'right' && !expandedDock)">keyboard_arrow_right</i>
                <i class="material-icons" v-if="(position === 'right' && !expandedDock) || (position !== 'right' && expandedDock)">keyboard_arrow_left</i>
            </button>
            <div class="dock-tab" :class="privateScope.selectedSelectable(tab)" :style="tabSizeStyle" v-for="tab in tabs" :key="tab.id" @click="selectTabWithId(tab.id)">
                <span class="dock-tab-compact" :style="'width: ' + compactSize" v-html="tab.compact" v-if="typeof tab.compact === 'string'"></span>
                <div class="dock-tab-compact" :style="'width: ' + compactSize" v-else>
                    <component :is="tab.compact.componentName" v-bind="tab.compact.props"></component>
                </div>
                <div class="dock-tab-expanded" :style="expandedSizeStyle" v-if="expandedDock">
                    <span v-html="tab.expanded" v-if="typeof tab.expanded === 'string'"></span>
                    <div v-else>
                        <component :is="tab.expanded.componentName" v-bind="tab.expanded.props"></component>
                    </div>
                </div>
            </div>
            <div class="footer-tabs">
                <div :class="privateScope.selectedSelectable(tab)" class="footer-tab" :style="footerSizeStyle" v-for="tab in footerTabs" :key="tab.id" @click="selectTabWithId(tab.id)">
                    <span class="footer-tab-compact" :style="'width: ' + compactSize" v-html="tab.compact" v-if="typeof tab.compact === 'string'"></span>
                    <div class="footer-tab-compact" :style="'width: ' + compactSize" v-else>
                        <component :is="tab.compact.componentName" v-bind="tab.compact.props"></component>
                    </div>
                    <div class="footer-tab-expanded" :style="expandedSizeStyle" v-if="expandedDock && (position === 'left' || position === 'right')">
                        <span v-html="tab.expanded" v-if="typeof tab.expanded === 'string'"></span>
                        <div v-else>
                            <component :is="tab.expanded.componentName" v-bind="tab.expanded.props"></component>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content" :style="contentMarginStyle">
            <div class="dock-tab-content" v-for="tab in headerTabs" :key="tab.id" v-show="tab.content && (tab.id === selectedTab)">
                <div v-if="typeof tab.content === 'string'" v-html="tab.content"></div>
                <component v-else :is="tab.content.componentName" v-bind="tab.content.props"></component>
            </div>
            <div class="dock-tab-content" v-for="tab in tabs" :key="tab.id" v-show="tab.content && (tab.id === selectedTab)">
                <div v-if="typeof tab.content === 'string'" v-html="tab.content"></div>
                <component v-else :is="tab.content.componentName" v-bind="tab.content.props"></component>
            </div>
            <div class="dock-tab-content" v-for="tab in footerTabs" :key="tab.id" v-show="tab.content && (tab.id === selectedTab)">
                <div v-if="typeof tab.content === 'string'" v-html="tab.content"></div>
                <component v-else :is="tab.content.componentName" v-bind="tab.content.props"></component>
            </div>
        </div>
        <div v-show="false" class="original-dom">
            <slot></slot>
        </div>
    </div>
</template>

<style lang="scss" scoped>
    @import './Dock.scss';
</style>

<script src="./Dock.component.js"></script>
