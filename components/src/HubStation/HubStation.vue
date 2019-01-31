<template>
    <div class="hub-station-component">
        <header v-if="isHeaderEnabled" class="hub-station-bar hub-station-bar--header">
            <hub-dock ref="dockTop" class="hub-dock hub-dock--top" :expanded="true" :expandable="false"
                      position="top">
                <hub-element v-for="(elem, index) in configData.top" :key="`hub-elem-${index}`">
                    <template v-if="elem.compact" slot="compact-label">
                        <component :is="elem.compact.componentName" v-bind="elem.compact.props"></component>
                    </template>
                    <template v-if="elem.expanded" slot="expanded-label">
                        <component :is="elem.expanded.componentName" v-bind="elem.expanded.props"></component>
                    </template>
                    <template v-if="elem.content" slot="content">
                        <component :is="elem.content.componentName" v-bind="elem.content.props"></component>
                    </template>
                </hub-element>
            </hub-dock>
        </header>
        <section class="hub-station-center-area">
            <aside v-if="isLeftEnabled" class="hub-station-aside hub-station-left">
                <hub-dock ref="dockLeft" @tabSelected="_onDockTabSelected(DockPosition.LEFT, $event)"
                          class="hub-dock hub-dock--left" position="left">
                    <hub-element v-for="(elem, index) in configData.left" :key="`hub-elem-${index}`">
                        <template v-if="elem.compact" slot="compact-label">
                            <component :is="elem.compact.componentName" v-bind="elem.compact.props"></component>
                        </template>
                        <template v-if="elem.expanded" slot="expanded-label">
                            <component :is="elem.expanded.componentName" v-bind="elem.expanded.props"></component>
                        </template>
                        <template v-if="elem.content" slot="content">
                            <component :is="elem.content.componentName" v-bind="elem.content.props"></component>
                        </template>
                    </hub-element>
                </hub-dock>
            </aside>
            <section class="hub-station-content">
                <!--<router-multi-view class="hub-station-content-view"></router-multi-view>-->
            </section>
            <aside v-if="isRightEnabled" class="hub-station-right">
                <hub-dock ref="dockRight"
                          @tabSelected="_onDockTabSelected(DockPosition.RIGHT, $event)" class="hub-dock hub-dock--right"
                           position="right">
                    <hub-element v-for="(elem, index) in configData.right" :key="`hub-elem-${index}`">
                        <template v-if="elem.compact" slot="compact-label">
                            <component :is="elem.compact.componentName" v-bind="elem.compact.props"></component>
                        </template>
                        <template v-if="elem.expanded" slot="expanded-label">
                            <component :is="elem.expanded.componentName" v-bind="elem.expanded.props"></component>
                        </template>
                        <template v-if="elem.content" slot="content">
                            <component :is="elem.content.componentName" v-bind="elem.content.props"></component>
                        </template>
                    </hub-element>
                </hub-dock>
            </aside>
        </section>
        <footer v-if="isFooterEnabled" class="hub-station-bar hub-station-bar--footer">
            <hub-dock ref="dockBottom"
                      @tabSelected="_onDockTabSelected(DockPosition.BOTTOM, $event)" class="hub-dock hub-dock--bottom"
                      :expandable="false" position="bottom">
                <hub-element v-for="(elem, index) in configData.bottom" :key="`hub-elem-${index}`">
                    <template v-if="elem.compact" slot="compact-label">
                        <component :is="elem.compact.componentName" v-bind="elem.compact.props"></component>
                    </template>
                    <template v-if="elem.expanded" slot="expanded-label">
                        <component :is="elem.expanded.componentName" v-bind="elem.expanded.props"></component>
                    </template>
                    <template v-if="elem.content" slot="content">
                        <component :is="elem.content.componentName" v-bind="elem.content.props"></component>
                    </template>
                </hub-element>
            </hub-dock>
        </footer>
    </div>
</template>
<!-- CSS to this component only -->
<style scoped lang="scss">
    @import "./HubStation.scss";
</style>
<!-- Global CSS -->
<style lang="scss">
</style>
<script lang="ts" src="./HubStation.component.ts">
</script>
